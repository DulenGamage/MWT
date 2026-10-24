<!-- app_core/modules/inventory/views/index.php -->
<?php
require_once __DIR__ . '/../controllers/InventoryController.php';
$invController = new InventoryController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $invController->adjustStock($_POST);
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . '?module=inventory');
    exit;
}

$products = $invController->getProductsForSelect();
$logs = $invController->getLogs();
?>

<div style="font-family: Arial, sans-serif; position: relative; margin-top: 20px;">
    
    <!-- Top Bar with Header and Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Inventory Stock Management</h2>
        <button onclick="toggleStockForm()" style="padding: 10px 18px; background: #17a2b8; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 14px;">
            + New Stock Movement
        </button>
    </div>

    <!-- Hidden/Collapsible Stock Adjustment Form Modal Box -->
    <div id="addStockModal" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0;">Record Stock Movement (Stock-In / Stock-Out)</h3>
            <button onclick="toggleStockForm()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>

        <form action="?module=inventory" method="POST" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px;">
            
            <select name="product_id" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Current: <?= $p['qty'] ?>)</option>
                <?php endforeach; ?>
            </select>

            <select name="type" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="IN">Stock IN (+)</option>
                <option value="OUT">Stock OUT (-)</option>
            </select>

            <input type="number" name="quantity" placeholder="Quantity" required min="1" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            
            <input type="text" name="reference" placeholder="Ref / PO No." style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">

            <textarea name="notes" placeholder="Reason / Notes (Optional)" style="grid-column: span 4; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>

            <button type="submit" name="adjust_stock" style="grid-column: span 4; padding: 12px; background: #17a2b8; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 15px;">Save Stock Movement</button>
        </form>
    </div>

    <!-- Stock Movement Audit Log Table -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
            <tr style="background: #f1f3f5; text-align: left;">
                <th>Date / Time</th>
                <th>Product</th>
                <th>Movement</th>
                <th>Qty</th>
                <th>Reference</th>
                <th>Notes</th>
                <th style="text-align: center; width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= htmlspecialchars($log['created_at']) ?></small></td>
                        <td>
                            <strong><?= htmlspecialchars($log['product_name']) ?></strong><br>
                            <small style="color: #666;"><?= htmlspecialchars($log['sku']) ?></small>
                        </td>
                        <td>
                            <?php if ($log['type'] === 'IN'): ?>
                                <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: bold;">STOCK IN</span>
                            <?php else: ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: bold;">STOCK OUT</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= (int)$log['quantity'] ?></strong></td>
                        <td><?= htmlspecialchars($log['reference'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($log['notes'] ?: '-') ?></td>
                        <td style="text-align: center;">
                            <span title="Logged entry (Audit)" style="color: #888; font-size: 14px;">🔒</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color: #777; padding: 20px;">No stock movements recorded yet. Click "+ New Stock Movement" above to add one.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Toggle Script for Inventory Form -->
<script>
function toggleStockForm() {
    var modal = document.getElementById('addStockModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
    }
}
</script>