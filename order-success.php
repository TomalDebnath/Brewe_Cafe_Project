<?php
require_once __DIR__ . '/config.php';
require_login();
$order = $_SESSION['last_order'] ?? null;
if (!$order) redirect('index.php');
unset($_SESSION['last_order']);
$pageTitle = 'Order received'; require __DIR__ . '/includes/header.php';
?>
<section class="success-wrap"><div class="success-icon">✓</div><span class="eyebrow">THANK YOU FOR CHOOSING BREWÉ</span><h1>Your order is <em>in.</em></h1><p>We have received your order and will prepare it with care.</p><div class="success-ticket"><span>ORDER NUMBER</span><strong><?= e($order['number']) ?></strong><div><span>PAYMENT METHOD</span><b><?= e($order['payment']) ?></b></div><div><span>TOTAL</span><b>৳<?= number_format((float)$order['total'],0) ?></b></div><small>Payme<small style="color:green;font-weight:bold;">
    ✓ Payment Successful
</small>nt status: Pending.</small></div><a class="button button-dark" href="<?= BASE_URL ?>/menu.php">Back to menu →</a></section>

<div class="preparation-time" style="
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin: 25px auto;
    padding: 20px;
    background: #fff1f1;
    border: 1px solid #ffcccc;
    border-radius: 12px;
    max-width: 420px;
">

    <span style="font-size: 38px;">⏱</span>

    <div>
        <strong style="
            display: block;
            color: #c62828;
            font-size: 23px;
            font-weight: 700;
        ">
            Estimated Preparation Time
        </strong>

        <p style="
            color: #e53935;
            font-size: 21px;
            font-weight: 700;
            margin: 8px 0 0;
        ">
            15–20 minutes
        </p>
    </div>

</div>



<?php require __DIR__ . '/includes/footer.php'; ?>
