<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Coffee, made personal';
$items = db()->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY FIELD(category,'Coffee','Tea','Pastry','Dessert'), id LIMIT 4")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-image" role="img" aria-label="Coffee cups on a cafe table"></div>
  <div class="hero-copy">
    <div><span class="eyebrow">A LITTLE PLACE TO FEEL AT HOME</span><h1>Every cup feels<br><em>like home.</em></h1></div>
    <div class="hero-note"><p>Freshly brewed coffee, handcrafted treats, and a space made for slow mornings and meaningful conversations.</p><a class="outline-button" href="<?= BASE_URL ?>/menu.php">Explore menu <span>→</span></a></div>
  </div>
</section>
<section class="section intro" id="story">
  <span class="eyebrow">WELCOME TO BREWÉ</span>
  <h2>Take a moment.<br><em>Make it yours.</em></h2>
  <p>From the first sip of espresso to the last bite of something sweet, we're here to make your campus day a little warmer.</p>
</section>
<section class="section menu-preview">
  <div class="section-heading"><div><span class="eyebrow">MADE FRESH, SERVED WITH CARE</span><h2>Guest favourites</h2></div><a class="text-link" href="<?= BASE_URL ?>/menu.php">View full menu →</a></div>
  <div class="product-grid">
    <?php foreach ($items as $item): ?>
      <article class="product-card">
        <a href="<?= BASE_URL ?>/menu.php"><img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy"></a>
        <div class="product-meta"><div><h3><?= e($item['name']) ?></h3><p><?= e($item['description']) ?></p></div><strong>৳<?= number_format((float)$item['price'], 0) ?></strong></div>
        <?php if ((int)$item['available'] === 1): ?><a class="small-link" href="<?= BASE_URL ?>/menu.php">Discover item ↗</a><?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<section class="quote-band"><p>“Coffee is a pause button for the everyday.”</p><span>— THE BREWÉ WAY</span></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
