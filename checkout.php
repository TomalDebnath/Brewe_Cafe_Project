
<?php
require_once __DIR__ . '/config.php';
require_login();

$token = cart_token();

$stmt = db()->prepare(
    'SELECT c.menu_item_id,c.quantity,m.name,m.price,m.available,m.stock_quantity
     FROM cart_items c
     JOIN menu_items m ON m.id=c.menu_item_id
     WHERE c.session_token=?'
);

$stmt->execute([$token]);
$items = $stmt->fetchAll();

if (!$items) {
    flash('Your bag is empty.');
    redirect('menu.php');
}

$total = 0;

foreach ($items as $it) {
    $total += (float)$it['price'] * (int)$it['quantity'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $customer = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $table = trim($_POST['table_no'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment = $_POST['payment_method'] ?? 'Cash';

    $bkashTrxId = strtoupper(
        trim($_POST['bkash_transaction_id'] ?? '')
    );

    if (
        $customer === '' ||
        $phone === '' ||
        !in_array($payment, ['Cash', 'Card', 'bKash'], true) ||
        ($payment === 'bKash' &&
        !preg_match('/^[A-Z0-9]{6,20}$/', $bkashTrxId))
    ) {
        $error = 'Please provide valid customer details and payment information.';
    } else {

        try {

            $pdo = db();
            $pdo->beginTransaction();

            $check = $pdo->prepare(
                'SELECT m.id,m.name,m.price,m.available,m.stock_quantity,c.quantity
                 FROM cart_items c
                 JOIN menu_items m ON m.id=c.menu_item_id
                 WHERE c.session_token=? FOR UPDATE'
            );

            $check->execute([$token]);
            $locked = $check->fetchAll();

            if (!$locked) {
                throw new RuntimeException('Your cart is empty.');
            }

            $freshTotal = 0;

            foreach ($locked as $line) {

                if (
                    !(int)$line['available'] ||
                    (int)$line['stock_quantity'] < (int)$line['quantity']
                ) {
                    throw new RuntimeException(
                        $line['name'] . ' is no longer available.'
                    );
                }

                $freshTotal +=
                    (float)$line['price'] * (int)$line['quantity'];
            }

            $orderNo = 'BW' . date('ymd') .
                strtoupper(bin2hex(random_bytes(2)));

      // Demo payment processing

// Demo payment processing
$payStatus = 'Paid';

$ins = $pdo->prepare(
    "INSERT INTO orders
    (order_number, user_id, customer_name, phone, email,
     table_no, notes, total, status, payment_method, payment_status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)"
);

$ins->execute([
    $orderNo,
    current_user()['id'],
    $customer,
    $phone,
    $email,
    $table,
    $notes,
    $freshTotal,
    $payment,
    $payStatus
]);

            $orderId = (int)$pdo->lastInsertId();

            $oi = $pdo->prepare(
                'INSERT INTO order_items
                (order_id,menu_item_id,name,price,quantity)
                VALUES (?,?,?,?,?)'
            );

            $stock = $pdo->prepare(
                'UPDATE menu_items
                 SET stock_quantity=stock_quantity-?
                 WHERE id=? AND stock_quantity>=?'
            );

            foreach ($locked as $line) {

                $oi->execute([
                    $orderId,
                    $line['id'],
                    $line['name'],
                    $line['price'],
                    $line['quantity']
                ]);

                $stock->execute([
                    $line['quantity'],
                    $line['id'],
                    $line['quantity']
                ]);

                if ($stock->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Stock changed. Please try again.'
                    );
                }
            }

            $pdo->prepare(
                'DELETE FROM cart_items WHERE session_token=?'
            )->execute([$token]);

            $pdo->commit();

            $_SESSION['last_order'] = [
                'number' => $orderNo,
                'total' => $freshTotal,
                'payment' => $payment,
                'transaction_id' =>
                    $payment === 'bKash' ? $bkashTrxId : ''
            ];

            redirect('order-success.php');

        } catch (Throwable $ex) {

            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $ex instanceof RuntimeException
                ? $ex->getMessage()
                : 'We could not place your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero compact-hero">
    <span class="eyebrow">ALMOST YOURS</span>
    <h1>Checkout.</h1>
</section>

<section class="section checkout-section">

<?php if ($error): ?>
    <div class="error-message"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="checkout-layout">

<div class="checkout-form">

    <input type="hidden" name="csrf"
           value="<?= e(csrf_token()) ?>">

    <h2>Your details</h2>

    <label>Customer name
        <input name="customer_name" required
               value="<?= e(current_user()['name']) ?>">
    </label>

    <div class="two-fields">

        <label>Phone number
            <input name="phone" required
                   placeholder="01XXXXXXXXX">
        </label>

        <label>Email (optional)
            <input type="email" name="email"
                   placeholder="you@example.com">
        </label>

    </div>

    <label>Table number / pickup note
        <input name="table_no"
               placeholder="e.g. Table 04 or Takeaway">
    </label>

    <label>Special instructions
        <textarea name="notes" rows="3"></textarea>
    </label>

    <h2>Payment method</h2>

    <div class="payment-options">

        <label>
            <input type="radio" name="payment_method"
                   value="Cash" checked>
            <span>
                <b>Cash</b>
                <small>Pay at the counter</small>
            </span>
        </label>

        <label>
            <input type="radio" name="payment_method"
                   value="Card">
            <span>
                <b>Card</b>
                <small>Demo selection · no charge</small>
            </span>
        </label>

        <label>
            <input type="radio" name="payment_method"
                   value="bKash">
            <span>
                <b>bKash</b>
                <small>Demo payment</small>
            </span>
        </label>

    </div>

    <div id="bkash-details"
         style="display:none; margin-top:20px;
         padding:20px; background:#fff4f8;
         border-radius:12px;">

        <h3 style="color:#d12053;">bKash Demo Payment</h3>

        <p>This is a demonstration only.
           No real money will be collected.</p>

        <label>Demo Transaction ID
            <input type="text"
                   name="bkash_transaction_id"
                   id="bkash_transaction_id"
                   placeholder="Enter demo Transaction ID">
        </label>

    </div>

    <button class="button button-brown full-button"
            type="submit">
        Place order →
    </button>

</div>

<aside class="summary-card">

    <span class="eyebrow">YOUR ORDER</span>

    <?php foreach ($items as $it): ?>

        <div class="summary-row">
            <span>
                <?= e($it['name']) ?>
                × <?= (int)$it['quantity'] ?>
            </span>

            <strong>
                ৳<?= number_format(
                    (float)$it['price'] * (int)$it['quantity'], 0
                ) ?>
            </strong>
        </div>

    <?php endforeach; ?>

    <div class="summary-row total-row">
        <span>Total</span>
        <strong>৳<?= number_format($total, 0) ?></strong>
    </div>

    <p class="fine-print">
        Your order is confirmed after stock verification.
    </p>

</aside>

</form>
</section>

<script>
const radios = document.querySelectorAll(
    'input[name="payment_method"]'
);

const details = document.getElementById('bkash-details');
const trx = document.getElementById('bkash_transaction_id');

function toggleBkash() {

    const selected = document.querySelector(
        'input[name="payment_method"]:checked'
    );

    const isBkash = selected && selected.value === 'bKash';

    details.style.display = isBkash ? 'block' : 'none';
    trx.required = isBkash;
}

radios.forEach(radio => {
    radio.addEventListener('change', toggleBkash);
});

toggleBkash();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
