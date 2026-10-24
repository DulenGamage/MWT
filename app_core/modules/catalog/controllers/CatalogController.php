<?php
// app_core/modules/catalog/controllers/CatalogController.php

require_once __DIR__ . '/../../../config/database.php';

class CatalogController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index() {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function store($data, $file = null) {
        $imageName = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = time() . '_' . uniqid() . '.' . $ext;
            $uploadPath = __DIR__ . '/../../../../htdocs/assets/uploads/' . $imageName;
            
            if (!is_dir(dirname($uploadPath))) {
                mkdir(dirname($uploadPath), 0755, true);
            }
            move_uploaded_file($file['tmp_name'], $uploadPath);
        }

        $stmt = $this->db->prepare("INSERT INTO products (sku, name, category, qty, cost, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['sku'],
            $data['name'],
            $data['category'] ?? 'General',
            $data['qty'] ?? 0,
            $data['cost'] ?? 0.00,
            $data['price'],
            $data['description'] ?? '',
            $imageName
        ]);
    }
}