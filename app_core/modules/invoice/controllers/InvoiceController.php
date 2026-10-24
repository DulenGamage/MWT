<?php
// app_core/modules/invoice/controllers/InvoiceController.php

require_once __DIR__ . '/../../../config/database.php';

class InvoiceController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllInvoices() {
        $stmt = $this->db->query("SELECT * FROM invoices ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getInvoiceById($id) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if ($invoice) {
            $itemStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
            $itemStmt->execute([$id]);
            $invoice['items'] = $itemStmt->fetchAll();
        }
        return $invoice;
    }

    public function saveInvoice($data) {
        $mode = $data['mode'] ?? 'general';
        $invoiceNumber = trim($data['invoice_number']);
        $customerName = trim($data['customer_name']);
        $customerAddress = trim($data['customer_address'] ?? '');
        $shippingAddress = trim($data['shipping_address'] ?? '');
        $invoiceDate = $data['invoice_date'] ?? date('Y-m-d');
        $paymentTerms = trim($data['payment_terms'] ?? 'Cash');
        $deliveryCharge = (float)($data['delivery_charge'] ?? 0);
        $discount = (float)($data['discount'] ?? 0);
        $taxRate = (float)($data['tax_rate'] ?? 0);
        $amountPaid = (float)($data['amount_paid'] ?? 0);

        // Calculate items and subtotal
        $subtotal = 0;
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $desc = trim($item['description'] ?? '');
                if (empty($desc)) continue;
                $qty = (int)($item['qty'] ?? 1);
                $rate = (float)($item['rate'] ?? 0);
                $amount = $qty * $rate;
                $subtotal += $amount;
                $items[] = ['desc' => $desc, 'qty' => $qty, 'rate' => $rate, 'amount' => $amount];
            }
        }

        $taxAmount = $subtotal * ($taxRate / 100);
        $grandTotal = $subtotal + $deliveryCharge - $discount + $taxAmount;

        try {
            $this->db->beginTransaction();

            if (!empty($data['invoice_id'])) {
                // Update existing
                $invoiceId = (int)$data['invoice_id'];
                $stmt = $this->db->prepare("UPDATE invoices SET invoice_number=?, mode=?, customer_name=?, customer_address=?, shipping_address=?, invoice_date=?, payment_terms=?, subtotal=?, delivery_charge=?, discount=?, tax_rate=?, grand_total=?, amount_paid=? WHERE id=?");
                $stmt->execute([$invoiceNumber, $mode, $customerName, $customerAddress, $shippingAddress, $invoiceDate, $paymentTerms, $subtotal, $deliveryCharge, $discount, $taxRate, $grandTotal, $amountPaid, $invoiceId]);

                // Clear old items
                $del = $this->db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
                $del->execute([$invoiceId]);
            } else {
                // Insert new
                $stmt = $this->db->prepare("INSERT INTO invoices (invoice_number, mode, customer_name, customer_address, shipping_address, invoice_date, payment_terms, subtotal, delivery_charge, discount, tax_rate, grand_total, amount_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$invoiceNumber, $mode, $customerName, $customerAddress, $shippingAddress, $invoiceDate, $paymentTerms, $subtotal, $deliveryCharge, $discount, $taxRate, $grandTotal, $amountPaid]);
                $invoiceId = $this->db->lastInsertId();
            }

            // Insert items
            $itemStmt = $this->db->prepare("INSERT INTO invoice_items (invoice_id, item_description, quantity, rate, amount) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $it) {
                $itemStmt->execute([$invoiceId, $it['desc'], $it['qty'], $it['rate'], $it['amount']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}