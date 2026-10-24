<?php
// app_core/modules/dispatch/controllers/DispatchController.php

require_once __DIR__ . '/../../../config/database.php';

class DispatchController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get all dispatch records with linked invoice details and line items
    public function getAllDispatches() {
        $stmt = $this->db->query("
            SELECT d.*, i.invoice_number, i.customer_name, i.customer_address, i.invoice_date, i.grand_total, i.amount_paid 
            FROM dispatches d 
            JOIN invoices i ON d.invoice_id = i.id 
            ORDER BY d.id DESC
        ");
        $dispatches = $stmt->fetchAll();

        foreach ($dispatches as &$disp) {
            $itemStmt = $this->db->prepare("SELECT item_description, quantity FROM invoice_items WHERE invoice_id = ?");
            $itemStmt->execute([$disp['invoice_id']]);
            $disp['products'] = $itemStmt->fetchAll();
        }

        return $dispatches;
    }

    // Get invoices available to dispatch
    public function getInvoicesForDispatch() {
        $stmt = $this->db->query("SELECT id, invoice_number, customer_name, grand_total FROM invoices ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // Create a new dispatch entry
    public function createDispatch($data) {
        $invoiceId = (int)$data['invoice_id'];
        $courierName = trim($data['courier_name'] ?? 'Hand Delivery');
        $trackingNumber = trim($data['tracking_number'] ?? '');
        $dispatchDate = $data['dispatch_date'] ?? date('Y-m-d');
        $deliveryStatus = $data['delivery_status'] ?? 'Pending';
        $paymentStatus = $data['payment_status'] ?? 'Unpaid';
        $notes = trim($data['notes'] ?? '');

        if ($invoiceId <= 0) return false;

        $stmt = $this->db->prepare("INSERT INTO dispatches (invoice_id, courier_name, tracking_number, dispatch_date, delivery_status, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$invoiceId, $courierName, $trackingNumber, $dispatchDate, $deliveryStatus, $paymentStatus, $notes]);
    }

    // Quick Update Status via Smart Chip Dropdowns
    public function updateStatus($dispatchId, $field, $value) {
        if (!in_array($field, ['delivery_status', 'payment_status'])) return false;
        $stmt = $this->db->prepare("UPDATE dispatches SET {$field} = ? WHERE id = ?");
        return $stmt->execute([$value, (int)$dispatchId]);
    }

    // Delete Dispatch Record
    public function deleteDispatch($id) {
        $stmt = $this->db->prepare("DELETE FROM dispatches WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}