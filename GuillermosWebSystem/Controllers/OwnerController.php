<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

class OwnerController
{
    private \mysqli $conn;

    public function __construct()
    {
        global $conn;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($conn) || !$conn instanceof \mysqli) {
            throw new \RuntimeException('Database connection not available.');
        }

        $this->conn = $conn;
    }

    public function handleAjax(): void
    {
        $action = $_GET['action'] ?? $_POST['action'] ?? null;
        if ($action === null) {
            return;
        }

        switch ($action) {
            case 'stats':
                $this->jsonResponse([
                    'status' => 'success',
                    'data' => $this->getDashboardStats(),
                ]);
                break;
            case 'inventory':
                $category = $_GET['category'] ?? $_POST['category'] ?? null;
                $this->jsonResponse([
                    'status' => 'success',
                    'data' => $this->getInventory($category),
                ]);
                break;
            case 'performance':
                $category = $_GET['category'] ?? $_POST['category'] ?? 'all';
                $this->jsonResponse([
                    'status' => 'success',
                    'data' => $this->getProductPerformance($category),
                ]);
                break;
            case 'create-product':
                $this->requirePost();
                $payload = $this->getRequestData();
                $productData = $this->validateProductInput($payload);
                $newId = $this->insertProduct($productData);
                $product = $this->getProductById($newId);
                if ($product === null) {
                    $this->serverError('Unable to fetch newly created product.');
                }

                $this->jsonResponse([
                    'status' => 'success',
                    'data' => $product,
                ]);
                break;
            case 'update-product':
                $this->requirePost();
                $payload = $this->getRequestData();
                $productId = isset($payload['Product_ID']) ? (int)$payload['Product_ID'] : 0;
                if ($productId <= 0) {
                    $this->validationError('Product ID is required.');
                }

                if ($this->getProductById($productId) === null) {
                    $this->notFound('Product not found.');
                }

                $productData = $this->validateProductInput($payload);
                $this->updateProduct($productId, $productData);
                $product = $this->getProductById($productId);
                if ($product === null) {
                    $this->notFound('Product not found.');
                }

                $this->jsonResponse([
                    'status' => 'success',
                    'data' => $product,
                ]);
                break;
            case 'delete-product':
                $this->requirePost();
                $payload = $this->getRequestData();
                $productId = isset($payload['Product_ID']) ? (int)$payload['Product_ID'] : 0;
                if ($productId <= 0) {
                    $this->validationError('Product ID is required.');
                }

                if (!$this->deleteProduct($productId)) {
                    $this->notFound('Product not found.');
                }

                $this->jsonResponse([
                    'status' => 'success',
                ]);
                break;
            default:
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Unsupported action.',
                ], 400);
        }
    }

    public function getDashboardStats(): array
    {
        return [
            'total_customers' => $this->countCustomers(),
            'total_orders' => $this->countOrders(),
            'total_delivered' => $this->countDeliveredOrders(),
            'total_revenue' => $this->calculateRevenue(),
        ];
    }

    public function getInventory(?string $category = null): array
    {
        if (!$this->tableExists('product')) {
            return [];
        }

        $columns = ['Product_ID', 'Product_Name', 'Category', 'Price'];
        foreach (['Description', 'Sub_category', 'Stock_Quantity', 'Low_Stock_Alert'] as $column) {
            if ($this->columnExists('product', $column)) {
                $columns[] = $column;
            }
        }

        $columnSql = implode(', ', array_map(static fn (string $col) => '`' . $col . '`', $columns));
        $sql = "SELECT {$columnSql} FROM product";

        if ($category !== null && $category !== '') {
            $sql .= ' WHERE Category = ?';
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                return [];
            }

            $stmt->bind_param('s', $category);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();

            return array_map([$this, 'normalizeProductRow'], $data);
        }

        $result = $this->conn->query($sql);
        if ($result === false) {
            return [];
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();

        return array_map([$this, 'normalizeProductRow'], $data);
    }

    public function getProductPerformance(string $category = 'all'): array
    {
        if (!$this->tableExists('orders')) {
            return [];
        }

        $productMap = $this->getProductMap();
        $performance = [];

        $result = $this->conn->query('SELECT order_json FROM orders');
        if ($result === false) {
            return [];
        }

        while ($row = $result->fetch_assoc()) {
            $items = json_decode($row['order_json'] ?? '[]', true);
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $name => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                $price = isset($item['price']) ? (float)$item['price'] : 0.0;

                if ($quantity <= 0) {
                    continue;
                }

                $info = $productMap[$name] ?? null;

                if ($info !== null) {
                    $price = $price > 0 ? $price : (float)($info['Price'] ?? 0);
                }

                if (!isset($performance[$name])) {
                    $performance[$name] = [
                        'id' => $info['Product_ID'] ?? null,
                        'name' => $name,
                        'cat' => $info['Category'] ?? 'Uncategorized',
                        'price' => $price,
                        'sales' => 0,
                        'revenue' => 0.0,
                        'rating' => 0,
                        'reviews' => 0,
                    ];
                }

                $performance[$name]['sales'] += $quantity;
                $performance[$name]['revenue'] += $quantity * $price;
            }
        }

        $result->free();

        if ($category !== 'all') {
            $performance = array_filter(
                $performance,
                static fn (array $item) => strcasecmp($item['cat'] ?? '', $category) === 0
            );
        }

        uasort($performance, static fn (array $a, array $b) => $b['sales'] <=> $a['sales']);

        // Enrich fallback rating and reviews if not provided
        foreach ($performance as &$item) {
            $item['sales'] = (int)$item['sales'];
            $item['revenue'] = (float)$item['revenue'];
            if ($item['rating'] <= 0) {
                $item['rating'] = 4.5;
            }

            if ($item['reviews'] <= 0) {
                $item['reviews'] = max(1, (int)round($item['sales'] * 0.3));
            }
        }
        unset($item);

        return array_values($performance);
    }

    private function countCustomers(): int
    {
        if (!$this->tableExists('users')) {
            return 0;
        }

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE user_role = 'customer'");
        if ($result === false) {
            return 0;
        }

        $row = $result->fetch_assoc();
        $result->free();

        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    private function countOrders(): int
    {
        if (!$this->tableExists('orders')) {
            return 0;
        }

        $result = $this->conn->query('SELECT COUNT(*) AS total FROM orders');
        if ($result === false) {
            return 0;
        }

        $row = $result->fetch_assoc();
        $result->free();

        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    private function countDeliveredOrders(): int
    {
        if (!$this->tableExists('orders')) {
            return 0;
        }

        foreach (['status', 'order_status', 'delivery_status'] as $column) {
            if ($this->columnExists('orders', $column)) {
                $sql = "SELECT COUNT(*) AS total FROM orders WHERE LOWER({$column}) = 'delivered'";
                $result = $this->conn->query($sql);
                if ($result === false) {
                    return 0;
                }

                $row = $result->fetch_assoc();
                $result->free();

                return isset($row['total']) ? (int)$row['total'] : 0;
            }
        }

        // Fallback: assume delivered equals total orders when no status column exists
        return $this->countOrders();
    }

    private function calculateRevenue(): float
    {
        if (!$this->tableExists('orders')) {
            return 0.0;
        }

        $result = $this->conn->query('SELECT SUM(subtotal + delivery_fee) AS revenue FROM orders');
        if ($result === false) {
            return 0.0;
        }

        $row = $result->fetch_assoc();
        $result->free();

        return isset($row['revenue']) ? (float)$row['revenue'] : 0.0;
    }

    private function getProductMap(): array
    {
        if (!$this->tableExists('product')) {
            return [];
        }

        $result = $this->conn->query('SELECT Product_ID, Product_Name, Category, Price FROM product');
        if ($result === false) {
            return [];
        }

        $map = [];
        while ($row = $result->fetch_assoc()) {
            $map[$row['Product_Name']] = $row;
        }

        $result->free();

        return $map;
    }

    private function requirePost(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
        if ($method !== 'POST') {
            header('Allow: POST');
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'POST method required.',
            ], 405);
        }
    }

    private function getRequestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function validateProductInput(array $input): array
    {
        $name = trim((string)($input['Product_Name'] ?? ''));
        if ($name === '') {
            $this->validationError('Product name is required.');
        }

        $category = trim((string)($input['Category'] ?? ''));
        if ($category === '') {
            $this->validationError('Category is required.');
        }

        $priceValue = $input['Price'] ?? null;
        if ($priceValue === '' || $priceValue === null || !is_numeric($priceValue)) {
            $this->validationError('Price must be a valid number.');
        }
        $price = round((float)$priceValue, 2);
        if ($price < 0) {
            $this->validationError('Price cannot be negative.');
        }

        $stockValue = $input['Stock_Quantity'] ?? null;
        if ($stockValue === '' || $stockValue === null || filter_var($stockValue, FILTER_VALIDATE_INT) === false) {
            $this->validationError('Stock quantity must be an integer.');
        }
        $stock = (int)$stockValue;
        if ($stock < 0) {
            $this->validationError('Stock quantity cannot be negative.');
        }

        $description = isset($input['Description']) ? trim((string)$input['Description']) : '';
        $description = $description === '' ? null : $description;

        $subCategory = isset($input['Sub_category']) ? trim((string)$input['Sub_category']) : '';
        $subCategory = $subCategory === '' ? null : $subCategory;

        $lowAlert = trim((string)($input['Low_Stock_Alert'] ?? 'Safe'));
        if ($lowAlert === '') {
            $lowAlert = 'Safe';
        }
        $allowedAlerts = ['Safe', 'Low', 'Critical'];
        if (!in_array($lowAlert, $allowedAlerts, true)) {
            $lowAlert = 'Safe';
        }

        return [
            'Product_Name' => $name,
            'Description' => $description,
            'Category' => $category,
            'Sub_category' => $subCategory,
            'Price' => $price,
            'Stock_Quantity' => $stock,
            'Low_Stock_Alert' => $lowAlert,
        ];
    }

    private function insertProduct(array $data): int
    {
        $stmt = $this->conn->prepare('INSERT INTO product (Product_Name, Description, Category, Sub_category, Price, Stock_Quantity, Low_Stock_Alert) VALUES (?,?,?,?,?,?,?)');
        if ($stmt === false) {
            $this->serverError('Database error preparing insert statement.');
        }

        $name = $data['Product_Name'];
        $description = $data['Description'];
        $category = $data['Category'];
        $subCategory = $data['Sub_category'];
        $price = $data['Price'];
        $stock = $data['Stock_Quantity'];
        $lowAlert = $data['Low_Stock_Alert'];

        $stmt->bind_param('ssssdis', $name, $description, $category, $subCategory, $price, $stock, $lowAlert);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            $this->serverError('Failed to save product: ' . $error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return (int)$id;
    }

    private function updateProduct(int $productId, array $data): void
    {
        $stmt = $this->conn->prepare('UPDATE product SET Product_Name = ?, Description = ?, Category = ?, Sub_category = ?, Price = ?, Stock_Quantity = ?, Low_Stock_Alert = ? WHERE Product_ID = ?');
        if ($stmt === false) {
            $this->serverError('Database error preparing update statement.');
        }

        $name = $data['Product_Name'];
        $description = $data['Description'];
        $category = $data['Category'];
        $subCategory = $data['Sub_category'];
        $price = $data['Price'];
        $stock = $data['Stock_Quantity'];
        $lowAlert = $data['Low_Stock_Alert'];

        $stmt->bind_param('ssssdisi', $name, $description, $category, $subCategory, $price, $stock, $lowAlert, $productId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            $this->serverError('Failed to update product: ' . $error);
        }

        $stmt->close();
    }

    private function deleteProduct(int $productId): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM product WHERE Product_ID = ?');
        if ($stmt === false) {
            $this->serverError('Database error preparing delete statement.');
        }

        $stmt->bind_param('i', $productId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            $this->serverError('Failed to delete product: ' . $error);
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }

    private function getProductById(int $productId): ?array
    {
        $stmt = $this->conn->prepare('SELECT Product_ID, Product_Name, Description, Category, Sub_category, Price, Stock_Quantity, Low_Stock_Alert FROM product WHERE Product_ID = ? LIMIT 1');
        if ($stmt === false) {
            $this->serverError('Database error preparing lookup statement.');
        }

        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->normalizeProductRow($row);
    }

    private function normalizeProductRow(array $row): array
    {
        if (isset($row['Product_ID'])) {
            $row['Product_ID'] = (int)$row['Product_ID'];
        }

        if (isset($row['Price'])) {
            $row['Price'] = (float)$row['Price'];
        }

        if (isset($row['Stock_Quantity'])) {
            $row['Stock_Quantity'] = (int)$row['Stock_Quantity'];
        }

        if (isset($row['Product_Name'])) {
            $row['Product_Name'] = trim((string)$row['Product_Name']);
        }

        if (isset($row['Category'])) {
            $row['Category'] = trim((string)$row['Category']);
        }

        if (!isset($row['Description']) || $row['Description'] === '') {
            $row['Description'] = null;
        }

        if (!isset($row['Sub_category']) || $row['Sub_category'] === '') {
            $row['Sub_category'] = null;
        }

        if (isset($row['Low_Stock_Alert'])) {
            $row['Low_Stock_Alert'] = trim((string)$row['Low_Stock_Alert']);
        }

        if (!isset($row['Low_Stock_Alert']) || $row['Low_Stock_Alert'] === '') {
            $row['Low_Stock_Alert'] = 'Safe';
        }

        return $row;
    }

    private function validationError(string $message): void
    {
        $this->jsonResponse([
            'status' => 'error',
            'message' => $message,
        ], 422);
    }

    private function notFound(string $message = 'Resource not found.'): void
    {
        $this->jsonResponse([
            'status' => 'error',
            'message' => $message,
        ], 404);
    }

    private function serverError(string $message): void
    {
        $this->jsonResponse([
            'status' => 'error',
            'message' => $message,
        ], 500);
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->conn->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param('s', $table);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    private function columnExists(string $table, string $column): bool
    {
        $stmt = $this->conn->prepare('SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1');
        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($payload);
        exit;
    }
}
