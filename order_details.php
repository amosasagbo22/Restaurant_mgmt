<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$pdo = get_pdo();

$order_id = (int)get('id', 0);
if (!$order_id) {
    redirect('orders.php');
}

// Get order details
$order = $pdo->prepare('SELECT * FROM orders WHERE id = ?')->execute([$order_id])->fetch();
if (!$order) {
    redirect('orders.php');
}

// Get order items
$items = $pdo->prepare('
    SELECT oi.*, m.name as item_name, m.description
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    WHERE oi.order_id = ?
    ORDER BY oi.id
')->execute([$order_id])->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<h2>Order Details #<?php echo $order_id; ?></h2>
<p><a class="btn" href="orders.php">← Back to Orders</a></p>

<div class="order-details">
    <div class="order-info">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?php echo h($order['customer_name']); ?></p>
        <p><strong>Phone:</strong> <?php echo h($order['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo h($order['address']); ?></p>
        <p><strong>Order Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
        <p><strong>Status:</strong> <?php echo h($order['status'] ?? 'pending'); ?></p>
    </div>

    <div class="order-items">
        <h3>Order Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo h($item['item_name']); ?></td>
                        <td><?php echo h($item['description']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td><?php echo format_currency((float)$item['unit_price']); ?></td>
                        <td><?php echo format_currency((float)$item['quantity'] * (float)$item['unit_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align:right">Order Total:</th>
                    <th><?php echo format_currency((float)$order['total_amount']); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?> 