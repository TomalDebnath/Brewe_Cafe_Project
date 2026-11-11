<?php
require_once __DIR__ . '/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0); $stock = max(0,(int)($_POST['stock_quantity'] ?? 0)); $available = isset($_POST['available']) ? 1 : 0;
    db()->prepare('UPDATE menu_items SET stock_quantity=?, available=? WHERE id=?')->execute([$stock,$available,$id]);
    flash('Product inventory updated.'); redirect('admin.php');
}
$products = db()->query('SELECT * FROM menu_items ORDER BY category,name')->fetchAll();
$orders = db()->query('SELECT id,order_number,customer_name,total,status,payment_method,payment_status,created_at FROM orders ORDER BY id DESC LIMIT 10')->fetchAll();
$pageTitle = 'Cafe administration'; require __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero"><span class="eyebrow">STAFF ONLY</span><h1>Cafe <em>dashboard.</em></h1><p>Manage menu availability and keep an eye on recent orders.</p></section>
<section class="section admin-section"><h2>Inventory management</h2><div class="admin-list">
<?php foreach ($products as $p): ?><form method="post" class="admin-product"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><div><strong><?= e($p['name']) ?></strong><small><?= e($p['category']) ?> · ৳<?= number_format((float)$p['price'],0) ?></small></div><label>Stock<input type="number" min="0" name="stock_quantity" value="<?= (int)$p['stock_quantity'] ?>"></label><label class="check-label"><input type="checkbox" name="available" value="1" <?= (int)$p['available'] ? 'checked' : '' ?>> Listed</label><button class="button button-dark" type="submit">Save</button></form><?php endforeach; ?>
</div><h2 class="admin-orders-title">Recent orders</h2><div class="table-wrap"><table class="orders-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($orders as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e($o['customer_name']) ?></td><td>৳<?= number_format((float)$o['total'],0) ?></td><td><?= e($o['payment_method']) ?></td><td><?= e($o['status']) ?></td><td><?= e($o['created_at']) ?></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="6">No orders yet.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
