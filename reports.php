<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$pdo = get_pdo();

// Get business analytics
$total_revenue = $pdo->query('SELECT SUM(total_amount) as total FROM orders')->fetch()['total'] ?? 0;
$total_orders = $pdo->query('SELECT COUNT(*) as count FROM orders')->fetch()['count'] ?? 0;
$total_customers = $pdo->query('SELECT COUNT(DISTINCT customer_name) as count FROM orders')->fetch()['count'] ?? 0;
$total_reservations = $pdo->query('SELECT COUNT(*) as count FROM reservations')->fetch()['count'] ?? 0;

// Top selling items
$top_items = $pdo->query('
    SELECT m.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.unit_price) as revenue
    FROM menu_items m
    LEFT JOIN order_items oi ON m.id = oi.menu_item_id
    GROUP BY m.id, m.name
    ORDER BY total_sold DESC
    LIMIT 10
')->fetchAll();

// Revenue by category
$category_revenue = $pdo->query('
    SELECT c.name, SUM(oi.quantity * oi.unit_price) as revenue
    FROM categories c
    LEFT JOIN menu_items m ON c.id = m.category_id
    LEFT JOIN order_items oi ON m.id = oi.menu_item_id
    GROUP BY c.id, c.name
    ORDER BY revenue DESC
')->fetchAll();

// Recent orders
$recent_orders = $pdo->query('
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 10
')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<h2>Business Reports & Analytics</h2>
<p><a class="btn" href="dashboard.php">Back to Dashboard</a></p>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Orders</h3>
        <div class="stat-number"><?php echo $total_orders; ?></div>
    </div>
    <div class="stat-card">
        <h3>Unique Customers</h3>
        <div class="stat-number"><?php echo $total_customers; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Reservations</h3>
        <div class="stat-number"><?php echo $total_reservations; ?></div>
    </div>
</div>

<div class="reports-grid">
    <div class="report-card">
        <h3>Top Selling Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_items as $item): ?>
                    <tr>
                        <td><?php echo h($item['name']); ?></td>
                        <td><?php echo (int)$item['total_sold']; ?></td>
                        <td>$<?php echo number_format($item['revenue'] ?? 0, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="report-card">
        <h3>Revenue by Category</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($category_revenue as $cat): ?>
                    <tr>
                        <td><?php echo h($cat['name']); ?></td>
                        <td>$<?php echo number_format($cat['revenue'] ?? 0, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="report-card">
    <h3>Recent Orders</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td><?php echo h($order['customer_name']); ?></td>
                    <td><?php echo (int)$order['item_count']; ?> items</td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?> 