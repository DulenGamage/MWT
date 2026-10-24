<!-- app_core/modules/catalog/views/index.php -->
<div style="font-family: Arial, sans-serif; position: relative;">
    
    <!-- Top Bar with Header and Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Product Catalogue Management</h2>
        <button onclick="toggleAddForm()" style="padding: 10px 18px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 14px;">
            + Add New Product
        </button>
    </div>

    <!-- Hidden/Collapsible Add Product Form Modal Box -->
    <div id="addProductModal" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ddd; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin:0;">Add New Product to Inventory</h3>
            <button onclick="toggleAddForm()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <form action="" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <input type="text" name="sku" placeholder="SKU (e.g., KMT-001)" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <input type="text" name="name" placeholder="Product Name" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <input type="text" name="category" placeholder="Category" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            
            <input type="number" name="qty" placeholder="Initial Quantity (Qty)" required min="0" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <input type="number" step="0.01" name="cost" placeholder="Cost Price (LKR)" required min="0" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <input type="number" step="0.01" name="price" placeholder="Selling Price (LKR)" required min="0" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            
            <textarea name="description" placeholder="Product Description" style="grid-column: span 3; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
            
            <div style="grid-column: span 3;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Product Picture:</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <button type="submit" name="add_product" style="grid-column: span 3; padding: 12px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-size: 16px;">Save Product</button>
        </form>
    </div>

    <!-- Products List Table -->
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
            <tr style="background: #f1f3f5; text-align: left;">
                <th>Picture</th>
                <th>SKU & Name</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Cost</th>
                <th>Selling Price</th>
                <th style="text-align: center; width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td style="text-align: center; width: 60px;">
                            <?php if ($p['image']): ?>
                                <img src="/assets/uploads/<?= htmlspecialchars($p['image']) ?>" width="45" height="45" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            <?php else: ?>
                                <span style="color:#aaa; font-size: 11px;">No Img</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                            <small style="color: #666;">SKU: <?= htmlspecialchars($p['sku']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>
                            <span style="background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-weight: bold;">
                                <?= (int)$p['qty'] ?>
                            </span>
                        </td>
                        <td style="color: #444;">LKR <?= number_format($p['cost'], 2) ?></td>
                        <td style="color: #007bff; font-weight: bold;">LKR <?= number_format($p['price'], 2) ?></td>
                        <td style="text-align: center;">
                            <!-- Edit action button icon link -->
                            <a href="?edit=<?= $p['id'] ?>" title="Edit Product" aria-label="Edit Product" style="text-decoration: none; font-size: 18px; padding: 4px 8px; background: #ffc107; color: #fff; border-radius: 4px;"><i class="bi bi-pencil-square"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color: #777; padding: 20px;">No products added yet. Click "+ Add New Product" above to start.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Simple Toggle Script -->
<script>
function toggleAddForm() {
    var modal = document.getElementById('addProductModal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
    }
}
</script>