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
           case 'adjust-stock':
            $this->requirePost();
            $payload = $this->getRequestData();
            $productId = (int)($payload['Product_ID'] ?? 0);
            $change    = (int)($payload['Quantity_Changed'] ?? 0);
            $force     = !empty($payload['forceUpdate']);

            if ($productId <= 0 || $change === 0) {
                $this->validationError('Invalid data.');
            }

            $product = $this->getProductById($productId);
            if (!$product) {
                $this->notFound('Product not found.');
            }

            $newStock = $product['Stock_Quantity'] + $change;

            if ($newStock < 0 && !$force) {
                $this->validationError('Stock cannot go negative. Use “Force” if needed.');
            }
            $this->updateProduct($productId, [
                'Product_Name'     => $product['Product_Name'],
                'Description'      => $product['Description'],
                'Category'         => $product['Category'],
                'Sub_category'     => $product['Sub_category'],
                'Price'            => $product['Price'],
                'Stock_Quantity'   => max(0, $newStock),   
                'Low_Stock_Alert'  => $this->computeAlert(max(0, $newStock)),
            ]);

            $this->jsonResponse([
                'status' => 'success',
                'data'   => $this->getProductById($productId)
            ]);
            break;

        case 'inventory-log':
            $productId = (int)($_GET['product_id'] ?? 0);
            $limit     = (int)($_GET['limit'] ?? 50);
            $this->jsonResponse([
                'status' => 'success',
                'data'   => $this->getInventoryLog($productId, $limit)
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
        $sql = "SELECT 
                    Product_ID, 
                    Product_Name, 
                    Category, 
                    Price, 
                    IFNULL(Description, '') AS Description,
                    IFNULL(Sub_category, '') AS Sub_category,
                    Stock_Quantity
                FROM product";

        $params = [];
        $types  = '';

        if ($category !== null && $category !== '') {
            $sql .= " WHERE Category = ?";
            $params[] = $category;
            $types   .= 's';
        }

        $sql .= " ORDER BY Product_ID ASC";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return [];

    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

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
// Auto-compute Low_Stock_Alert based on stock, changes 11-17-25//

   private function computeAlert(int $stock): string
{
    if ($stock >= 20) {
        return 'Safe';
    }
    if ($stock >= 10) {
        return 'Low';
    }
    if ($stock >= 1) {
        return 'Critical';
    }
    return 'Out of Stock';
}

    private function validateProductInput(array $input): array
    {
        $name = trim((string)($input['Product_Name'] ?? ''));
        if ($name === '') $this->validationError('Product name is required.');

        $category = trim((string)($input['Category'] ?? ''));
        if ($category === '') $this->validationError('Category is required.');

        $price = round((float)($input['Price'] ?? 0), 2);
        if ($price < 0) $this->validationError('Price cannot be negative.');

        $stock = (int)($input['Stock_Quantity'] ?? 0);
        if ($stock < 0) $this->validationError('Stock quantity cannot be negative.');

        $description = trim((string)($input['Description'] ?? '')) ?: null;
        $subCategory = trim((string)($input['Sub_category'] ?? '')) ?: null;

        return [
    'Product_Name'    => $name,
    'Description'     => $description,
    'Category'        => $category,
    'Sub_category'    => $subCategory,
    'Price'           => $price,
    'Stock_Quantity'  => $stock,
    'Low_Stock_Alert' => $this->computeAlert($stock), 
    ];
    }

    
    private function insertProduct(array $data): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO product 
                (Product_Name, Description, Category, Sub_category, Price, Stock_Quantity, Low_Stock_Alert) 
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->bind_param(
            'ssssdis',
            $data['Product_Name'],
            $data['Description'],
            $data['Category'],
            $data['Sub_category'],
            $data['Price'],
            $data['Stock_Quantity'],
            $data['Low_Stock_Alert']
        );

        $stmt->execute() || $this->serverError('Insert failed: ' . $stmt->error);
        $id = $stmt->insert_id;
        $stmt->close();
        return (int)$id;
    }

    private function updateProduct(int $productId, array $data): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE product 
                SET Product_Name = ?, Description = ?, Category = ?, Sub_category = ?, 
                    Price = ?, Stock_Quantity = ?, Low_Stock_Alert = ? 
              WHERE Product_ID = ?'
        );
        $stmt->bind_param(
            'ssssdisi',
            $data['Product_Name'],
            $data['Description'],
            $data['Category'],
            $data['Sub_category'],
            $data['Price'],
            $data['Stock_Quantity'],
            $data['Low_Stock_Alert'],
            $productId
        );

        $stmt->execute() || $this->serverError('Update failed: ' . $stmt->error);
        $stmt->close();
    }

    private function deleteProduct(int $productId): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM product WHERE Product_ID = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

   private function getProductById(int $productId): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT Product_ID, Product_Name, Description, Category, Sub_category, 
                    Price, Stock_Quantity 
               FROM product WHERE Product_ID = ? LIMIT 1'
        );
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $this->normalizeProductRow($row) : null;
    }

    private function normalizeProductRow(array $row): array
{
    $stock = (int)($row['Stock_Quantity'] ?? 0);

    $row['Product_ID']      = (int)($row['Product_ID'] ?? 0);
    $row['Price']           = (float)($row['Price'] ?? 0);
    $row['Stock_Quantity']  = $stock;
    $row['Product_Name']    = trim((string)($row['Product_Name'] ?? ''));
    $row['Category']        = trim((string)($row['Category'] ?? ''));
    $row['Description']     = $row['Description'] === '' ? null : trim($row['Description']);
    $row['Sub_category']    = $row['Sub_category'] === '' ? null : trim($row['Sub_category']);
    
    // auto detect stock alert, changes 11-17-25//
    $row['Low_Stock_Alert'] = $this->computeAlert($stock); 

    return $row;
}

    private function validationError(string $msg): void { $this->jsonResponse(['status'=>'error','message'=>$msg],422); }
    private function notFound(string $msg='Resource not found.'): void { $this->jsonResponse(['status'=>'error','message'=>$msg],404); }
    private function serverError(string $msg): void { $this->jsonResponse(['status'=>'error','message'=>$msg],500); }
    private function tableExists(string $table): bool
    {
        $stmt = $this->conn->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
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
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function jsonResponse(array $payload, int $code = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($payload);
        exit;
    }

private function getInventoryLog(int $productId = 0, int $limit = 50): array
    {
        $sql = 'SELECT l.*, p.Product_Name, u.Username AS Staff_Name
                FROM inventory_log l
                LEFT JOIN product p ON l.Product_ID = p.Product_ID
                LEFT JOIN users   u ON l.Staff_ID = u.User_ID
                WHERE 1=1';
        $params = []; $types = '';

        if ($productId > 0) { $sql .= ' AND l.Product_ID = ?'; $params[] = $productId; $types .= 'i'; }

        $sql .= ' ORDER BY l.Log_Date DESC, l.Log_ID DESC LIMIT ?';
        $params[] = $limit; $types .= 'i';

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res  = $stmt->get_result();
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return array_map(fn($r) => [
            'Log_ID'          => (int)$r['Log_ID'],
            'Product_ID'      => (int)$r['Product_ID'],
            'Product_Name'    => $r['Product_Name'],
            'Quantity_Changed'=> (int)$r['Quantity_Changed'],
            'Reason'          => $r['Reason'],
            'Log_Date'        => $r['Log_Date'],
            'Staff_ID'        => (int)$r['Staff_ID'],
            'Staff_Name'      => $r['Staff_Name'],
        ], $data);
    }
}
