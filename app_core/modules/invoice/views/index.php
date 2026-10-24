<!-- app_core/modules/invoice/views/index.php -->
<?php
require_once __DIR__ . '/../controllers/InvoiceController.php';
$invController = new InvoiceController();

$action = $_GET['action'] ?? 'list';
$editInvoice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_invoice'])) {
    $invController->saveInvoice($_POST);
    header("Location: ?module=invoice");
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editInvoice = $invController->getInvoiceById($_GET['id']);
}

$invoices = $invController->getAllInvoices();

// Fetch live products catalogue for quick selection
$catalogProducts = [];
try {
    $db = Database::getInstance();
    $catalogProducts = $db->query("SELECT sku, name, price FROM products ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}
?>

<div style="font-family: Arial, sans-serif; margin-top: 20px;">
    
    <?php if ($action === 'view' && isset($_GET['id'])): ?>
    <?php 
        $inv = $invController->getInvoiceById($_GET['id']);
        if (!$inv) { echo "<p>Invoice not found.</p>"; return; }
    ?>
    
    <!-- Action buttons hidden during print -->
    <div class="no-print" style="margin-bottom: 15px;">
        <a href="?module=invoice" style="text-decoration: none; padding: 8px 15px; background: #6c757d; color: white; border-radius: 4px; font-weight: bold;">&larr; Back to Invoices</a>
        <button onclick="window.print()" style="margin-left: 10px; padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Print / Download PDF</button>
    </div>

        <!-- PRINTABLE INVOICE TEMPLATE CONTAINER -->
        <div id="printableArea" style="background: white; padding: 40px; border: 1px solid #ddd; max-width: 800px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            
            <?php if ($inv['mode'] === 'kmt'): ?>
                <!-- KMT MODE HEADER -->
                <div style="border-bottom: 3px solid #ff6600; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h2 style="margin: 0; color: #ff6600; font-size: 24px;">kmt</h2>
                        <small style="letter-spacing: 2px; color: #333; font-weight: bold;">DISTRIBUTORS</small>
                        <h3 style="margin: 15px 0 5px 0; color: #222;">KMT Distributors Pvt Ltd</h3>
                        <p style="margin: 0; color: #555; font-size: 13px;">Ahangama Road, Yakkalamulla, Sri Lanka</p>
                        <p style="margin: 5px 0 0 0; color: #555; font-size: 13px;"><strong>Phone:</strong> +94 76 55 61 721 &nbsp;|&nbsp; <strong>Email:</strong> kmtonline@gmail.com</p>
                    </div>
                    <div style="text-align: right;">
                        <h1 style="margin: 0; color: #ff6600; font-size: 32px; letter-spacing: 1px;">INVOICE</h1>
                        <p style="margin: 10px 0 5px 0; font-size: 14px;"><strong>Invoice No:</strong> <?= htmlspecialchars($inv['invoice_number']) ?></p>
                        <p style="margin: 0; font-size: 14px; color: #666;"><strong>Date:</strong> <?= htmlspecialchars($inv['invoice_date']) ?></p>
                    </div>
                </div>

                <!-- Billed To / Shipped To -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div style="border: 1px solid #eee; padding: 15px; border-radius: 6px; background: #fafafa;">
                        <span style="color: #ff6600; font-weight: bold; font-size: 12px; display: block; margin-bottom: 5px;"> BILLED TO</span>
                        <strong style="font-size: 15px;"><?= htmlspecialchars($inv['customer_name']) ?></strong>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #555; white-space: pre-line;"><?= htmlspecialchars($inv['customer_address']) ?></p>
                    </div>
                    <div style="border: 1px solid #eee; padding: 15px; border-radius: 6px; background: #fafafa;">
                        <span style="color: #ff6600; font-weight: bold; font-size: 12px; display: block; margin-bottom: 5px;"> SHIPPED TO (OPTIONAL)</span>
                        <p style="margin: 0; font-size: 13px; color: #555; white-space: pre-line;"><?= htmlspecialchars($inv['shipping_address'] ?: 'Same as billing / Pickup') ?></p>
                    </div>
                </div>

            <?php else: ?>
                <!-- GENERAL MODE HEADER -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                    <div>
                        <p style="margin: 0; font-weight: bold; color: #333;">On Approval</p>
                    </div>
                    <div style="text-align: right;">
                        <h1 style="margin: 0; font-size: 28px; color: #222; letter-spacing: 1px;">INVOICE</h1>
                        <p style="margin: 5px 0; font-size: 14px; color: #555;"># <?= htmlspecialchars($inv['invoice_number']) ?></p>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                    <div>
                        <span style="color: #666; font-size: 13px;">Customer:</span><br>
                        <strong style="font-size: 16px;"><?= htmlspecialchars($inv['customer_name']) ?></strong>
                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #555; white-space: pre-line;"><?= htmlspecialchars($inv['customer_address']) ?></p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0 0 5px 0; font-size: 13px;">Date: &nbsp; <strong><?= htmlspecialchars($inv['invoice_date']) ?></strong></p>
                        <p style="margin: 0 0 5px 0; font-size: 13px;">Payment Terms: &nbsp; <strong><?= htmlspecialchars($inv['payment_terms']) ?></strong></p>
                        <div style="background: #f1f3f5; padding: 8px 15px; border-radius: 4px; display: inline-block; margin-top: 5px;">
                            <span style="font-size: 13px; font-weight: bold;">Balance Due:</span> &nbsp;
                            <strong style="font-size: 15px; color: #000;">LKR <?= number_format($inv['grand_total'] - $inv['amount_paid'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ITEMS TABLE -->
            <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="background: <?= $inv['mode'] === 'kmt' ? '#222' : '#343a40' ?>; color: white; text-align: left; font-size: 13px;">
                        <th style="padding: 10px;">Item / Description</th>
                        <th style="padding: 10px; width: 80px; text-align: center;">Quantity</th>
                        <th style="padding: 10px; width: 120px; text-align: right;">Rate (LKR)</th>
                        <th style="padding: 10px; width: 130px; text-align: right;">Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inv['items'] as $item): ?>
                        <tr style="border-bottom: 1px solid #eee; font-size: 13px;">
                            <td style="padding: 10px;"><?= htmlspecialchars($item['item_description']) ?></td>
                            <td style="padding: 10px; text-align: center;"><?= (int)$item['quantity'] ?></td>
                            <td style="padding: 10px; text-align: right;"><?= number_format($item['rate'], 2) ?></td>
                            <td style="padding: 10px; text-align: right; font-weight: bold;"><?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- FOOTER TOTALS & INSTRUCTIONS -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">
                <div style="width: 50%;">
                    <?php if ($inv['mode'] === 'kmt'): ?>
                        <div style="margin-bottom: 15px;">
                            <span style="font-weight: bold; color: #ff6600; font-size: 13px; display: block; margin-bottom: 4px;"> PAYMENT INSTRUCTIONS</span>
                            <p style="margin: 0; font-size: 12px; color: #555; line-height: 1.5;">
                                Bank: Commercial Bank<br>
                                Account Name: New Maweli Trading<br>
                                Account No: 1001099611<br>
                                Branch: Galle City Branch
                            </p>
                        </div>
                        <div>
                            <span style="font-weight: bold; color: #ff6600; font-size: 13px; display: block; margin-bottom: 4px;"> TERMS & POLICY</span>
                            <p style="margin: 0; font-size: 12px; color: #555;">Goods once sold are not returnable or refundable.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="width: 40%; text-align: right;">
                    <table width="100%" style="font-size: 13px; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 6px 0; color: #666;">Subtotal:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: bold;">LKR <?= number_format($inv['subtotal'], 2) ?></td>
                        </tr>
                        <?php if ($inv['delivery_charge'] > 0): ?>
                        <tr>
                            <td style="padding: 6px 0; color: #666;">Delivery Charge:</td>
                            <td style="padding: 6px 0; text-align: right;">LKR <?= number_format($inv['delivery_charge'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($inv['discount'] > 0): ?>
                        <tr>
                            <td style="padding: 6px 0; color: #666;">Discount:</td>
                            <td style="padding: 6px 0; text-align: right; color: #dc3545;">- LKR <?= number_format($inv['discount'], 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($inv['tax_rate'] > 0): ?>
                        <tr>
                            <td style="padding: 6px 0; color: #666;">Tax / VAT (<?= $inv['tax_rate'] ?>%):</td>
                            <td style="padding: 6px 0; text-align: right;">LKR <?= number_format($inv['subtotal'] * ($inv['tax_rate']/100), 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr style="border-top: 2px solid #333; border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px 0; font-weight: bold; font-size: 15px; color: <?= $inv['mode'] === 'kmt' ? '#ff6600' : '#000' ?>;">Grand Total:</td>
                            <td style="padding: 10px 0; text-align: right; font-weight: bold; font-size: 15px; color: <?= $inv['mode'] === 'kmt' ? '#ff6600' : '#000' ?>;">LKR <?= number_format($inv['grand_total'], 2) ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #666;">Amount Paid:</td>
                            <td style="padding: 6px 0; text-align: right;">LKR <?= number_format($inv['amount_paid'], 2) ?></td>
                        </tr>
                        <tr style="border-top: 1px solid #333;">
                            <td style="padding: 8px 0; font-weight: bold;">Balance Due:</td>
                            <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #007bff;">LKR <?= number_format($inv['grand_total'] - $inv['amount_paid'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Closing Note -->
            <div style="text-align: center; margin-top: 50px; border-top: 1px solid #eee; padding-top: 15px; font-size: 12px; color: #777;">
                Thank you for your business!<br>
            </div>
        </div>

    <?php else: ?>
        <!-- INVOICE LIST & CREATE/EDIT FORM INTERFACE -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">Invoice Management & Generator</h2>
            <button onclick="toggleInvoiceForm()" style="padding: 10px 18px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 14px;">
                + Create Invoice
            </button>
        </div>

        <!-- Hidden/Collapsible Create/Edit Invoice Modal Box -->
        <div id="invoiceModal" style="display: <?= $editInvoice ? 'block' : 'none' ?>; background: #f9f9f9; padding: 25px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin:0;"><?= $editInvoice ? 'Edit Invoice #' . $editInvoice['invoice_number'] : 'Create New Invoice' ?></h3>
                <button onclick="toggleInvoiceForm()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
            </div>

            <form action="?module=invoice" method="POST">
                <?php if ($editInvoice): ?>
                    <input type="hidden" name="invoice_id" value="<?= $editInvoice['id'] ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Invoice Mode:</label>
                        <select name="mode" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="general" <?= ($editInvoice && $editInvoice['mode'] === 'general') ? 'selected' : '' ?>>General (No Branding)</option>
                            <option value="kmt" <?= ($editInvoice && $editInvoice['mode'] === 'kmt') ? 'selected' : '' ?>>KMT Branded</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Invoice Number:</label>
                        <input type="text" name="invoice_number" value="<?= $editInvoice ? htmlspecialchars($editInvoice['invoice_number']) : 'INV-' . date('Y') . '-' . rand(100,999) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Invoice Date:</label>
                        <input type="date" name="invoice_date" value="<?= $editInvoice ? $editInvoice['invoice_date'] : date('Y-m-d') ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Customer Name:</label>
                        <input type="text" name="customer_name" value="<?= $editInvoice ? htmlspecialchars($editInvoice['customer_name']) : '' ?>" placeholder="e.g. KMT Distributors" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Billing Address:</label>
                        <textarea name="customer_address" placeholder="Customer billing address" style="width: 100%; height: 38px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"><?= $editInvoice ? htmlspecialchars($editInvoice['customer_address']) : '' ?></textarea>
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Shipping Address (Optional):</label>
                        <textarea name="shipping_address" placeholder="Delivery destination" style="width: 100%; height: 38px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"><?= $editInvoice ? htmlspecialchars($editInvoice['shipping_address']) : '' ?></textarea>
                    </div>
                </div>

                <!-- DYNAMIC ITEMS BUILDER WITH DROPDOWN AUTOFILL -->
                <h4 style="margin: 20px 0 10px 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Line Items</h4>
                <div id="invoiceItemsList">
                    <?php if ($editInvoice && !empty($editInvoice['items'])): ?>
                        <?php foreach ($editInvoice['items'] as $idx => $it): ?>
                            <div class="item-row" style="display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 40px; gap: 10px; margin-bottom: 10px;">
                                <select onchange="autoFillProduct(this, <?= $idx ?>)" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                    <option value="">-- Select from Catalogue --</option>
                                    <?php foreach ($catalogProducts as $cp): ?>
                                        <option value="<?= htmlspecialchars($cp['price']) ?>" data-name="<?= htmlspecialchars($cp['name']) ?>"><?= htmlspecialchars($cp['name']) ?> (LKR <?= number_format($cp['price'],2) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="desc_<?= $idx ?>" name="items[<?= $idx ?>][description]" value="<?= htmlspecialchars($it['item_description']) ?>" placeholder="Item Description" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <input type="number" name="items[<?= $idx ?>][qty]" value="<?= (int)$it['quantity'] ?>" placeholder="Qty" required min="1" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <input type="number" step="0.01" id="rate_<?= $idx ?>" name="items[<?= $idx ?>][rate]" value="<?= $it['rate'] ?>" placeholder="Rate (LKR)" required min="0" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <button type="button" onclick="this.parentElement.remove()" style="background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="item-row" style="display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 40px; gap: 10px; margin-bottom: 10px;">
                            <select onchange="autoFillProduct(this, 0)" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">-- Select from Catalogue --</option>
                                <?php foreach ($catalogProducts as $cp): ?>
                                    <option value="<?= htmlspecialchars($cp['price']) ?>" data-name="<?= htmlspecialchars($cp['name']) ?>"><?= htmlspecialchars($cp['name']) ?> (LKR <?= number_format($cp['price'],2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" id="desc_0" name="items[0][description]" placeholder="Item Description" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <input type="number" name="items[0][qty]" placeholder="Qty" value="1" required min="1" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <input type="number" step="0.01" id="rate_0" name="items[0][rate]" placeholder="Rate (LKR)" required min="0" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <button type="button" onclick="this.parentElement.remove()" style="background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">&times;</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="addInvoiceRow()" style="padding: 6px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin-bottom: 20px;">+ Add Line Item</button>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; background: #eee; padding: 15px; border-radius: 6px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:3px;">Delivery Charge (LKR):</label>
                        <input type="number" step="0.01" name="delivery_charge" value="<?= $editInvoice ? $editInvoice['delivery_charge'] : '0.00' ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:3px;">Discount (LKR):</label>
                        <input type="number" step="0.01" name="discount" value="<?= $editInvoice ? $editInvoice['discount'] : '0.00' ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:3px;">Tax / VAT (%):</label>
                        <input type="number" step="0.01" name="tax_rate" value="<?= $editInvoice ? $editInvoice['tax_rate'] : '0.00' ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:bold; margin-bottom:3px;">Amount Paid (LKR):</label>
                        <input type="number" step="0.01" name="amount_paid" value="<?= $editInvoice ? $editInvoice['amount_paid'] : '0.00' ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>
                </div>

                <button type="submit" name="save_invoice" style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 16px;">Save & Generate Invoice</button>
            </form>
        </div>

        <!-- Invoices List Table -->
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr style="background: #f1f3f5; text-align: left;">
                    <th>Invoice No</th>
                    <th>Mode</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Grand Total</th>
                    <th>Timestamp</th>
                    <th style="text-align: center; width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($inv['invoice_number']) ?></strong></td>
                            <td>
                                <?php if ($inv['mode'] === 'kmt'): ?>
                                    <span style="background: #ffe3d0; color: #c85000; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">KMT BRANDED</span>
                                <?php else: ?>
                                    <span style="background: #e2e8f0; color: #333; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">GENERAL</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                            <td><?= htmlspecialchars($inv['invoice_date']) ?></td>
                            <td style="font-weight: bold; color: #007bff;">LKR <?= number_format($inv['grand_total'], 2) ?></td>
                            <td><small><?= htmlspecialchars($inv['created_at']) ?></small></td>
                            <td style="text-align: center;">
                                <a href="?module=invoice&action=view&id=<?= $inv['id'] ?>" title="View Invoice" aria-label="View Invoice" style="text-decoration: none; padding: 4px 6px; background: #17a2b8; color: #fff; border-radius: 4px; font-size: 14px;"><i class="bi bi-eye"></i></a>
                                <a href="?module=invoice&action=edit&id=<?= $inv['id'] ?>" title="Edit Invoice" aria-label="Edit Invoice" style="text-decoration: none; padding: 4px 6px; background: #ffc107; color: #fff; border-radius: 4px; font-size: 14px; margin-left: 4px;"><i class="bi bi-pencil-square"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color: #777; padding: 20px;">No invoices generated yet. Click "+ Create Invoice" above to start.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function toggleInvoiceForm() {
    var modal = document.getElementById('invoiceModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
    }
}

function autoFillProduct(selectElement, index) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let price = selectedOption.value;
    let name = selectedOption.getAttribute('data-name');
    
    if (name) {
        document.getElementById('desc_' + index).value = name;
        document.getElementById('rate_' + index).value = price;
    }
}

let rowIndex = <?= $editInvoice && !empty($editInvoice['items']) ? count($editInvoice['items']) : 1 ?>;
function addInvoiceRow() {
    let container = document.getElementById('invoiceItemsList');
    let row = document.createElement('div');
    row.className = 'item-row';
    row.style.cssText = 'display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 40px; gap: 10px; margin-bottom: 10px;';
    
    let catalogOptions = `<option value="">-- Select from Catalogue --</option>`;
    <?php foreach ($catalogProducts as $cp): ?>
        catalogOptions += `<option value="<?= htmlspecialchars($cp['price']) ?>" data-name="<?= htmlspecialchars($cp['name']) ?>"><?= htmlspecialchars($cp['name']) ?> (LKR <?= number_format($cp['price'],2) ?>)</option>`;
    <?php endforeach; ?>

    row.innerHTML = `
        <select onchange="autoFillProduct(this, ${rowIndex})" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            ${catalogOptions}
        </select>
        <input type="text" id="desc_${rowIndex}" name="items[${rowIndex}][description]" placeholder="Item Description" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        <input type="number" name="items[${rowIndex}][qty]" placeholder="Qty" value="1" required min="1" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        <input type="number" step="0.01" id="rate_${rowIndex}" name="items[${rowIndex}][rate]" placeholder="Rate (LKR)" required min="0" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="button" onclick="this.parentElement.remove()" style="background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">&times;</button>
    `;
    container.appendChild(row);
    rowIndex++;
}
</script>