<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$pdo = get_pdo();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)post('order_id');
    $status = post('status');
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $order_id]);
    $message = 'Order status updated';
}

// Get all orders with customer details
$orders = $pdo->query('
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_items
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<h2>Order Management</h2>
<p><a class="btn" href="dashboard.php">Back to Dashboard</a></p>

<?php if (!empty($message)): ?>
    <div class="alert success"><?php echo h($message); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Orders</h3>
        <div class="stat-number"><?php echo count($orders); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="stat-number">$<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></div>
    </div>
    <div class="stat-card">
        <h3>Average Order</h3>
        <div class="stat-number">$<?php echo count($orders) > 0 ? number_format(array_sum(array_column($orders, 'total_amount')) / count($orders), 2) : '0.00'; ?></div>
    </div>
</div>

<h3>All Orders</h3>
<table class="table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Items</th>
            <th>Total</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?php echo (int)$order['id']; ?></td>
                <td><?php echo h($order['customer_name']); ?></td>
                <td><?php echo h($order['phone']); ?></td>
                <td><?php echo (int)$order['item_count']; ?> items</td>
                <td><?php echo format_currency((float)$order['total_amount']); ?></td>
                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../partials/footer.php'; ?> 