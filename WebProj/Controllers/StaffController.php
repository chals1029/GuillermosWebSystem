<?php
declare(strict_types=1);

require_once __DIR__ . '/../Config.php';

class StaffController
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
    }
   public function getInventoryProducts(): array
    {
        $sql = "SELECT 
                    Product_ID,
                    Product_Name,
                    Category,
                    Price,
                    Stock_Quantity,
                    Low_Stock_Alert
                FROM product 
                ORDER BY Product_ID ASC";

        $result = $this->conn->query($sql);
        $products = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $this->normalizeProductRow($row);
            }
            $result->free();
        }

        return $products;
   }

    private function getProductById(int $productId): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT Product_ID, Product_Name, Description, Category, Sub_category, 
                    Price, Stock_Quantity, Low_Stock_Alert
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

        $row['Product_ID']       = (int)($row['Product_ID'] ?? 0);
        $row['Price']            = (float)($row['Price'] ?? 0);
        $row['Stock_Quantity']   = $stock;
        $row['Product_Name']     = trim($row['Product_Name'] ?? '');
        $row['Category']         = trim($row['Category'] ?? '');
        $row['Low_Stock_Alert']  = $this->computeAlert($stock); // ← Always recompute!

        return $row;
    }

    private function computeAlert(int $stock): string
    {
        if ($stock >= 20) return 'Safe';
        if ($stock >= 10) return 'Low';
        if ($stock >= 1)  return 'Critical';
        return 'Out of Stock';
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}