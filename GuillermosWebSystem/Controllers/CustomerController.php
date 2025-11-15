<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

class CustomerController
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
        if (isset($_POST['action'], $_POST['product'])) {
            $this->handleCartAction($_POST['action'], $_POST['product']);
            return;
        }

        if (isset($_POST['checkout'], $_POST['order_data'])) {
            $this->handleCheckout($_POST['order_data']);
            return;
        }

        http_response_code(400);
        $this->jsonResponse([
            'status' => 'error',
            'message' => 'Unsupported request.',
        ]);
    }

    public function getProductsByCategory(string $category): array
    {
        $category = trim($category);

        if ($category === 'all') {
            $sql = 'SELECT Product_Name, Description, Price, Category FROM product ORDER BY Product_Name ASC';
            $result = $this->conn->query($sql);
            if ($result === false) {
                return [];
            }

            return $result->fetch_all(MYSQLI_ASSOC);
        }

        $sql = 'SELECT Product_Name, Description, Price, Category FROM product WHERE Category = ? ORDER BY Product_Name ASC';
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $products;
    }

    public function getCart(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public function countCartItems(array $cart): int
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += (int)($item['quantity'] ?? 0);
        }

        return $total;
    }

    private function handleCartAction(string $action, string $productName): void
    {
        $action = strtolower(trim($action));
        $productName = trim($productName);

        if ($productName === '') {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo '0';
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (!isset($_SESSION['cart'][$productName]) && $action === 'increase') {
            $product = $this->getProductByName($productName);
            if ($product === null) {
                http_response_code(404);
                header('Content-Type: text/plain');
                echo (string)$this->countCartItems($_SESSION['cart']);
                return;
            }

            $_SESSION['cart'][$productName] = [
                'price' => (float)$product['Price'],
                'quantity' => 0,
            ];
        }

        if (!isset($_SESSION['cart'][$productName])) {
            header('Content-Type: text/plain');
            echo (string)$this->countCartItems($_SESSION['cart']);
            return;
        }

        switch ($action) {
            case 'increase':
                $_SESSION['cart'][$productName]['quantity']++;
                break;
            case 'decrease':
                $_SESSION['cart'][$productName]['quantity']--;
                if ($_SESSION['cart'][$productName]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$productName]);
                }
                break;
            case 'remove':
                unset($_SESSION['cart'][$productName]);
                break;
        }

        header('Content-Type: text/plain');
        echo (string)$this->countCartItems($_SESSION['cart']);
        return;
    }

    private function handleCheckout(string $orderJson): void
    {
        $order = json_decode($orderJson, true);
        if (!is_array($order)) {
            http_response_code(400);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid order payload.',
            ]);
            return;
        }

        if (empty($order['customer_name']) || empty($order['items']) || !is_array($order['items'])) {
            http_response_code(400);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid order data.',
            ]);
            return;
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO orders 
            (customer_name, order_type, is_reservation, delivery_address, payment_method, subtotal, delivery_fee, order_json, created_at)
            VALUES (?,?,?,?,?,?,?, ?, NOW())'
        );

        if ($stmt === false) {
            http_response_code(500);
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Database error preparing statement.',
            ]);
            return;
        }

        $isReservation = !empty($order['is_reservation']) ? 1 : 0;
        $address = $order['delivery_address'] ?? '';
        $itemsJson = json_encode($order['items']);
        $paymentMethod = $order['payment_method'] ?? 'cash';
        $subtotal = isset($order['subtotal']) ? (float)$order['subtotal'] : 0.0;
        $deliveryFee = isset($order['delivery_fee']) ? (float)$order['delivery_fee'] : 0.0;

        $stmt->bind_param(
            'ssissdds',
            $order['customer_name'],
            $order['order_type'],
            $isReservation,
            $address,
            $paymentMethod,
            $subtotal,
            $deliveryFee,
            $itemsJson
        );

        if ($stmt->execute()) {
            $_SESSION['cart'] = [];
            $stmt->close();
            $this->jsonResponse([
                'status' => 'ok',
                'address' => $address,
            ]);
            return;
        }

        $error = $stmt->error;
        $stmt->close();
        http_response_code(500);
        $this->jsonResponse([
            'status' => 'error',
            'message' => 'Database error: ' . $error,
        ]);
    }

    private function getProductByName(string $productName): ?array
    {
        $stmt = $this->conn->prepare('SELECT Product_Name, Price FROM product WHERE Product_Name = ? LIMIT 1');
        if ($stmt === false) {
            return null;
        }

        $stmt->bind_param('s', $productName);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $product ?: null;
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($payload);
        exit;
    }
}
