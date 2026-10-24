<?php
// app_core/modules/inventory/controllers/InventoryController.php

require_once __DIR__ . '/../../../config/database.php';

class InventoryController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get all inventory adjustment logs
    public function getLogs() {
        $stmt = $this->db->query("
            SELECT il.*, p.name as product_name, p.sku 
            FROM inventory_logs il 
            JOIN products p ON il.product_id = p.id 
            ORDER BY il.id DESC
        ");
        return $stmt->fetchAll();
    }

    // Get product list for dropdown selection
    public function getProductsForSelect() {
        $stmt = $this->db->query("SELECT id, sku, name, qty FROM products ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    // Record a stock adjustment (IN or OUT)
    public function adjustStock($data) {
        $productId = (int)$data['product_id'];
        $type = $data['type']; // 'IN' or 'OUT'
        $qty = (int)$data['quantity'];
        $reference = trim($data['reference'] ?? '');
        $notes = trim($data['notes'] ?? '');

        if ($productId <= 0 || $qty <= 0) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert into inventory log
            $stmt = $this->db->prepare("INSERT INTO inventory_logs (product_id, type, quantity, reference, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$productId, $type, $qty, $reference, $notes]);

            // 2. Update product master qty
            if ($type === 'IN') {
                $updateStmt = $this->db->prepare("UPDATE products SET qty = qty + ? WHERE id = ?");
            } else {
                $updateStmt = $this->db->prepare("UPDATE products SET qty = GREATEST(0, qty - ?) WHERE id = ?");
            }
            $updateStmt->execute([$qty, $productId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}