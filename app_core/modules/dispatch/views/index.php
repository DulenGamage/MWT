<!-- app_core/modules/dispatch/views/index.php -->
<?php
require_once __DIR__ . '/../controllers/DispatchController.php';
$dispatchController = new DispatchController();

// Handle creating dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_dispatch'])) {
    $dispatchController->createDispatch($_POST);
    header("Location: ?module=dispatch");
    exit;
}

// Handle quick inline status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update'])) {
    $dispatchController->updateStatus($_POST['dispatch_id'], $_POST['field'], $_POST['value']);
    header("Location: ?module=dispatch");
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $dispatchController->deleteDispatch($_GET['delete']);
    header("Location: ?module=dispatch");
    exit;
}

$dispatches = $dispatchController->getAllDispatches();
$invoices = $dispatchController->getInvoicesForDispatch();
?>

<div style="font-family: Arial, sans-serif; margin-top: 20px;">
    
    <!-- Top Bar with Header and Toggle Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Dispatch Management</h2>
        <button onclick="toggleDispatchForm()" style="padding: 10px 18px; background: #fd7e14; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 14px;">
            + Create Dispatch Note
        </button>
    </div>

    <!-- Hidden/Collapsible Create Dispatch Modal Box -->
    <div id="dispatchModal" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0;">Create New Delivery Dispatch</h3>
            <button onclick="toggleDispatchForm()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>

        <form action="?module=dispatch" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px;">
            <div>
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Select Invoice:</label>
                <select name="invoice_id" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">-- Choose Invoice --</option>
                    <?php foreach ($invoices as $inv): ?>
                        <option value="<?= $inv['id'] ?>"><?= htmlspecialchars($inv['invoice_number']) ?> - <?= htmlspecialchars($inv['customer_name']) ?> (LKR <?= number_format($inv['grand_total'],2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Courier / Handler:</label>
                <input type="text" name="courier_name" placeholder="e.g. Domex / Hand Delivery" style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Tracking No (Optional):</label>
                <input type="text" name="tracking_number" placeholder="Tracking Number" style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Dispatch Date:</label>
                <input type="date" name="dispatch_date" value="<?= date('Y-m-d') ?>" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Delivery Status:</label>
                <select name="delivery_status" style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="Pending">Pending</option>
                    <option value="Dispatched">Dispatched</option>
                    <option value="Out for Delivery">Out for Delivery</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; font-weight:bold; font-size:12px; margin-bottom:5px;">Payment Status:</label>
                <select name="payment_status" style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partially Paid">Partially Paid</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>

            <textarea name="notes" placeholder="Special delivery instructions or driver notes..." style="grid-column: span 4; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>

            <button type="submit" name="save_dispatch" style="grid-column: span 4; padding: 12px; background: #fd7e14; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 15px;">Save Dispatch Note</button>
        </form>
    </div>

    <!-- Dispatch Records Table -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
            <tr style="background: #f1f3f5; text-align: left;">
                <th>Delivery Handler & Invoice</th>
                <th>Customer</th>
                <th>Products & Quantities</th>
                <th>Dispatch Date</th>
                <th>Delivery Status</th>
                <th>Payment Status</th>
                <th style="text-align: center; width: 60px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($dispatches)): ?>
                <?php foreach ($dispatches as $d): ?>
                    <tr>
                        <td>
                            <strong style="color: #222;"><?= htmlspecialchars($d['courier_name']) ?></strong><br>
                            <?php if ($d['tracking_number']): ?>
                                <small style="color: #666;">Track: <?= htmlspecialchars($d['tracking_number']) ?></small><br>
                            <?php endif; ?>
                            <!-- Clickable Link to Invoice View -->
                            <a href="?module=invoice&action=view&id=<?= $d['invoice_id'] ?>" target="_blank" style="color: #007bff; text-decoration: none; font-weight: bold; font-size: 13px;">
                                <i class="bi bi-receipt"></i> <?= htmlspecialchars($d['invoice_number']) ?> <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>

                        <td>
                            <strong><?= htmlspecialchars($d['customer_name']) ?></strong><br>
                            <small style="color: #666;"><?= htmlspecialchars($d['customer_address'] ?: 'No address') ?></small>
                        </td>

                        <td>
                            <ul style="margin: 0; padding-left: 15px; font-size: 13px;">
                                <?php foreach ($d['products'] as $p): ?>
                                    <li><?= htmlspecialchars($p['item_description']) ?> &times; <strong><?= $p['quantity'] ?></strong></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>

                        <td><small><?= htmlspecialchars($d['dispatch_date']) ?></small></td>

                        <!-- Interactive Delivery Status Smart Chip Dropdown -->
                        <td>
                            <form action="?module=dispatch" method="POST" style="margin:0;">
                                <input type="hidden" name="quick_update" value="1">
                                <input type="hidden" name="dispatch_id" value="<?= $d['id'] ?>">
                                <input type="hidden" name="field" value="delivery_status">
                                <select name="value" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; border: none; cursor: pointer;
                                    background: <?= $d['delivery_status'] === 'Delivered' ? '#d4edda' : ($d['delivery_status'] === 'Out for Delivery' ? '#cce5ff' : ($d['delivery_status'] === 'Dispatched' ? '#fff3cd' : '#f8d7da')) ?>;
                                    color: <?= $d['delivery_status'] === 'Delivered' ? '#155724' : ($d['delivery_status'] === 'Out for Delivery' ? '#004085' : ($d['delivery_status'] === 'Dispatched' ? '#856404' : '#721c24')) ?>;">
                                    <option value="Pending" <?= $d['delivery_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Dispatched" <?= $d['delivery_status'] === 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                    <option value="Out for Delivery" <?= $d['delivery_status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                    <option value="Delivered" <?= $d['delivery_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Returned" <?= $d['delivery_status'] === 'Returned' ? 'selected' : '' ?>>Returned</option>
                                </select>
                            </form>
                        </td>

                        <!-- Interactive Payment Status Smart Chip Dropdown -->
                        <td>
                            <form action="?module=dispatch" method="POST" style="margin:0;">
                                <input type="hidden" name="quick_update" value="1">
                                <input type="hidden" name="dispatch_id" value="<?= $d['id'] ?>">
                                <input type="hidden" name="field" value="payment_status">
                                <select name="value" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; border: none; cursor: pointer;
                                    background: <?= $d['payment_status'] === 'Paid' ? '#d4edda' : ($d['payment_status'] === 'Partially Paid' ? '#fff3cd' : '#f8d7da') ?>;
                                    color: <?= $d['payment_status'] === 'Paid' ? '#155724' : ($d['payment_status'] === 'Partially Paid' ? '#856404' : '#721c24') ?>;">
                                    <option value="Unpaid" <?= $d['payment_status'] === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                    <option value="Partially Paid" <?= $d['payment_status'] === 'Partially Paid' ? 'selected' : '' ?>>Partially Paid</option>
                                    <option value="Paid" <?= $d['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                </select>
                            </form>
                        </td>

                        <td style="text-align: center;">
                            <a href="?module=dispatch&delete=<?= $d['id'] ?>" onclick="return confirm('Remove dispatch log?')" title="Delete Record" aria-label="Delete Record" style="text-decoration: none; padding: 4px 6px; background: #dc3545; color: #fff; border-radius: 4px; font-size: 13px;"><i class="bi bi-trash3"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color: #777; padding: 20px;">No dispatch logs recorded yet. Click "+ Create Dispatch Note" above to start.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleDispatchForm() {
    var modal = document.getElementById('dispatchModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
    }
}
</script>