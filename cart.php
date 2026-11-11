<?php
require_once __DIR__ . '/config.php';
require_login();
$token = cart_token();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $itemId = (int)($_POST['item_id'] ?? 0);
    if ($action === 'add') {
        $p = db()->prepare('SELECT id, available, stock_quantity FROM menu_items WHERE id=?');
        $p->execute([$itemId]); $product = $p->fetch();
        if (!$product || !(int)$product['available'] || (int)$product['stock_quantity'] < 1) {
            flash('Sorry, this item is currently unavailable.');
        } else {
            $q = db()->prepare('SELECT quantity FROM cart_items WHERE session_token=? AND menu_item_id=? AND extra_sugar=0');
            $q->execute([$token,$itemId]); $existing = $q->fetchColumn();
            if ((int)$existing >= (int)$product['stock_quantity']) flash('You cannot add more than the available stock.');
            elseif ($existing !== false) { db()->prepare('UPDATE cart_items SET quantity=quantity+1 WHERE session_token=? AND menu_item_id=? AND extra_sugar=0')->execute([$token,$itemId]); flash('Added one more to your bag.'); }
            else { db()->prepare('INSERT INTO cart_items (session_token,menu_item_id,quantity) VALUES (?,?,1)')->execute([$token,$itemId]); flash('Added to your bag.'); }
        }
    } elseif (in_array($action, ['plus','minus','remove'], true)) {
        $q = db()->prepare('SELECT c.quantity,m.stock_quantity,m.available FROM cart_items c JOIN menu_items m ON m.id=c.menu_item_id WHERE c.session_token=? AND c.menu_item_id=?');
        $q->execute([$token,$itemId]); $line = $q->fetch();
        if ($line) {
            if ($action === 'remove' || ($action === 'minus' && (int)$line['quantity'] <= 1)) {
                db()->prepare('DELETE FROM cart_items WHERE session_token=? AND menu_item_id=?')->execute([$token,$itemId]);
            } elseif ($action === 'plus') {
                if ((int)$line['available'] && (int)$line['quantity'] < (int)$line['stock_quantity']) db()->prepare('UPDATE cart_items SET quantity=quantity+1 WHERE session_token=? AND menu_item_id=?')->execute([$token,$itemId]);
                else flash('Stock limit reached.');
            } else db()->prepare('UPDATE cart_items SET quantity=quantity-1 WHERE session_token=? AND menu_item_id=?')->execute([$token,$itemId]);
        }
    }
    redirect('cart.php');
}
$stmt = db()->prepare('SELECT c.menu_item_id,c.quantity,m.name,m.price,m.image,m.available,m.stock_quantity FROM cart_items c JOIN menu_items m ON m.id=c.menu_item_id WHERE c.session_token=? ORDER BY c.id DESC');
$stmt->execute([$token]); $items = $stmt->fetchAll();
$total = 0; foreach ($items as $it) $total += (float)$it['price'] * (int)$it['quantity'];
$pageTitle = 'Your bag'; require __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact-hero"><span class="eyebrow">YOUR BREWÉ SELECTION</span><h1>Your <em>bag.</em></h1></section>
<section class="section cart-section">
 <?php if (!$items): ?><div class="empty-state"><h2>Your bag is waiting.</h2><p>Find something lovely on our menu.</p><a class="button button-dark" href="<?= BASE_URL ?>/menu.php">Explore menu →</a></div>
 <?php else: ?>
 <div class="cart-layout"><div class="cart-lines">
 <?php foreach ($items as $it): ?>
  <article class="cart-line"><img src="<?= e($it['image']) ?>" alt="<?= e($it['name']) ?>"><div class="cart-product"><h3><?= e($it['name']) ?></h3><p>৳<?= number_format((float)$it['price'],0) ?> each</p><?php if (!(int)$it['available'] || (int)$it['stock_quantity'] < (int)$it['quantity']): ?><span class="sold-out-text">Stock changed — adjust quantity before checkout.</span><?php endif; ?>
   <div class="quantity-control">
    <?php foreach (['minus'=>'−','plus'=>'+'] as $act=>$label): ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= $act ?>"><input type="hidden" name="item_id" value="<?= (int)$it['menu_item_id'] ?>"><button aria-label="<?= $act ?> quantity"><?= $label ?></button></form><?php endforeach; ?>
    <span><?= (int)$it['quantity'] ?></span>
   </div>
  </div><strong>৳<?= number_format((float)$it['price']*(int)$it['quantity'],0) ?></strong>
  <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="item_id" value="<?= (int)$it['menu_item_id'] ?>"><button class="remove-link" type="submit">Remove</button></form></article>
 <?php endforeach; ?></div>
 <aside class="summary-card"><span class="eyebrow">ORDER SUMMARY</span><div class="summary-row"><span>Subtotal</span><strong>৳<?= number_format($total,0) ?></strong></div><div class="summary-row"><span>Service</span><span>Included</span></div><div class="summary-row total-row"><span>Total</span><strong>৳<?= number_format($total,0) ?></strong></div><a class="button button-brown full-button" href="<?= BASE_URL ?>/checkout.php">Continue to checkout →</a><a class="continue-link" href="<?= BASE_URL ?>/menu.php">← Continue shopping</a></aside></div>
 <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
