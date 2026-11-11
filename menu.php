<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Our Menu';
$category = $_GET['category'] ?? 'All';
$allowed = ['All','Coffee','Tea','Pastry','Dessert'];
if (!in_array($category, $allowed, true)) $category = 'All';
$sql = 'SELECT * FROM menu_items';
$params = [];
if ($category !== 'All') { $sql .= ' WHERE category = ?'; $params[] = $category; }
$sql .= " ORDER BY FIELD(category,'Coffee','Tea','Pastry','Dessert'), name";
$stmt = db()->prepare($sql); $stmt->execute($params); $items = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><span class="eyebrow">SOMETHING FOR EVERY MOOD</span><h1>Our <em>menu.</em></h1><p>Thoughtfully made coffee, tea and little treats.</p></section>
<section class="section menu-section">
  <div class="filter-row">
    <?php foreach ($allowed as $cat): ?><a class="filter-chip <?= $category === $cat ? 'active' : '' ?>" href="?category=<?= urlencode($cat) ?>"><?= e($cat) ?></a><?php endforeach; ?>
  </div>
  <div class="product-grid menu-grid">
    <?php foreach ($items as $item): $stock = (int)$item['stock_quantity']; $inStock = (int)$item['available'] === 1 && $stock > 0; ?>
      <article class="product-card menu-card">
        <div class="product-photo">
          <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
          <?php if (!$inStock): ?><span class="stock-badge sold-out">Currently unavailable</span><?php endif; ?>
        </div>
        <div class="product-meta"><div><span class="eyebrow"><?= e($item['category']) ?></span><h3><?= e($item['name']) ?></h3><p><?= e($item['description']) ?></p></div><strong>৳<?= number_format((float)$item['price'], 0) ?></strong></div>
        <?php if ($inStock): ?>
          <form class="add-form" method="post" action="<?= BASE_URL ?>/cart.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="add"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
            <button class="button button-dark" type="submit">Add to bag <span>＋</span></button>
          </form>
          <small class="stock-note"><?= $stock <= 5 ? 'Only ' . $stock . ' left' : 'Freshly prepared' ?></small>
        <?php else: ?><button class="button button-disabled" disabled>Out of stock</button><?php endif; ?>
      </article>
    <?php endforeach; ?>
    <?php if (!$items): ?><p>No products found in this category.</p><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
