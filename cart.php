<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION)) { session_start(); }
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$pdo = get_pdo();

// Handle add/remove/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_id'])) {
        $id = (int)$_POST['add_id'];
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        redirect('cart.php');
    }
    if (isset($_POST['update'])) {
        foreach ((array)($_POST['qty'] ?? []) as $id => $qty) {
            $id = (int)$id; $qty = max(0, (int)$qty);
            if ($qty === 0) { unset($_SESSION['cart'][$id]); } else { $_SESSION['cart'][$id] = $qty; }
        }
        redirect('cart.php');
    }
    if (isset($_POST['checkout'])) {
        // Create order
        $name = trim(post('name', ''));
        $phone = trim(post('phone', ''));
        $address = trim(post('address', ''));
        if ($name && $phone && $address && !empty($_SESSION['cart'])) {
            $ids = array_map('intval', array_keys($_SESSION['cart']));
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT id, price FROM menu_items WHERE id IN ($in)");
                $stmt->execute($ids);
                $prices = [];
                foreach ($stmt as $row) { $prices[(int)$row['id']] = (float)$row['price']; }
                $total = 0.0;
                foreach ($_SESSION['cart'] as $id => $qty) { $total += ($prices[$id] ?? 0) * $qty; }
                $pdo->beginTransaction();
                $ins = $pdo->prepare('INSERT INTO orders (customer_name, phone, address, total_amount) VALUES (?,?,?,?)');
                $ins->execute([$name, $phone, $address, $total]);
                $order_id = (int)$pdo->lastInsertId();
                $insItem = $pdo->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?,?,?,?)');
                foreach ($_SESSION['cart'] as $id => $qty) {
                    $insItem->execute([$order_id, $id, $qty, $prices[$id] ?? 0]);
                }
                $pdo->commit();
                $_SESSION['cart'] = [];
                $_SESSION['flash_success'] = 'Order placed successfully!';
                redirect('cart.php');
            }
        } else {
            $_SESSION['flash_error'] = 'Please fill all checkout fields and ensure your cart is not empty.';
            redirect('cart.php');
        }
    }
}

// Fetch cart items
$items = [];
$subtotal = 0.0;
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id IN ($in)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $id = (int)$row['id'];
        $qty = (int)($_SESSION['cart'][$id] ?? 0);
        $line = $qty * (float)$row['price'];
        $subtotal += $line;
        $items[] = [
            'id' => $id,
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'qty' => $qty,
            'line' => $line,
        ];
    }
}

include __DIR__ . '/partials/header.php';
?>

<h2>Cart</h2>

<?php if (!empty($_SESSION['flash_error'])): ?><div class="alert error"><?php echo h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div><?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?><div class="alert success"><?php echo h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div><?php endif; ?>

<?php if (!$items): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <form method="post">
        <table class="table">
            <thead>
                <tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php echo h($it['name']); ?></td>
                    <td><?php echo format_currency($it['price']); ?></td>
                    <td>
                        <input type="number" name="qty[<?php echo (int)$it['id']; ?>]" min="0" value="<?php echo (int)$it['qty']; ?>">
                    </td>
                    <td><?php echo format_currency($it['line']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><th colspan="3" style="text-align:right">Subtotal</th><th><?php echo format_currency($subtotal); ?></th></tr>
            </tfoot>
        </table>
        <p>
            <button type="submit" name="update" value="1">Update Cart</button>
        </p>
    </form>

    <h3>Checkout</h3>
    <form method="post">
        <div class="row">
            <div>
                <label>Name
                    <input type="text" name="name" required>
                </label>
            </div>
            <div>
                <label>Phone
                    <input type="text" name="phone" required>
                </label>
            </div>
        </div>
        <div class="row full">
            <div>
                <label>Address
                    <input type="text" name="address" required>
                </label>
            </div>
        </div>
        <p><button type="submit" name="checkout" value="1">Place Order</button></p>
    </form>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>

