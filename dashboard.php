<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$pdo = get_pdo();

// Handle create/update/delete menu items
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_item'])) {
        $name = trim(post('name', ''));
        $desc = trim(post('description', ''));
        $price = (float)post('price', 0);
        $cat = (int)post('category_id', 0);
        if ($name && $price > 0 && $cat > 0) {
            $stmt = $pdo->prepare('INSERT INTO menu_items (category_id, name, description, price) VALUES (?,?,?,?)');
            $stmt->execute([$cat, $name, $desc, $price]);
            $message = 'Item created';
        } else {
            $message = 'Please provide name, category and valid price';
        }
    }
    if (isset($_POST['delete_item'])) {
        $id = (int)post('id', 0);
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM menu_items WHERE name = ?');
            $stmt->execute([$id]);
            $message = 'Item deleted';
        }
    }
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$items = $pdo->query('SELECT m.id, m.name, m.price, c.name AS category FROM menu_items m JOIN categories c ON c.id=m.category_id ORDER BY c.name, m.name')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>
<h2>Restaurant Management Dashboard</h2>
<p><a class="btn" href="logout.php">Logout</a></p>

<div class="dashboard-nav">
    <a href="orders.php" class="nav-card">
        <h3>📋 Orders</h3>
        <p>Manage customer orders</p>
    </a>
    <a href="reservations.php" class="nav-card">
        <h3>📅 Reservations</h3>
        <p>View table bookings</p>
    </a>
    <a href="reports.php" class="nav-card">
        <h3>📊 Reports</h3>
        <p>Business analytics</p>
    </a>
</div>
<?php if ($message): ?><div class="alert success"><?php echo h($message); ?></div><?php endif; ?>

<h3>Create Menu Item</h3>
<form method="post">
    <div class="row">
        <div>
            <label>Name
                <input type="text" name="name" required>
            </label>
        </div>
        <div>
            <label>Category
                <select name="category_id" required>
                    <option value="">Select...</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>"><?php echo h($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </div>
    <div class="row">
        <div>
            <label>Price
                <input type="number" name="price" step="0.01" min="0" required>
            </label>
        </div>

    </div>
    <div class="row full">
        <div>
            <label>Description
                <textarea name="description" rows="3"></textarea>
            </label>
        </div>
    </div>
    <p><button type="submit" name="create_item" value="1">Create</button></p>
</form>

<h3>Menu Items</h3>
<table class="table">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?php echo (int)$it['id']; ?></td>
                <td><?php echo h($it['name']); ?></td>
                <td><?php echo h($it['category']); ?></td>
                <td><?php echo format_currency((float)$it['price']); ?></td>
                <td>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this item?');">
                        <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                        <button type="submit" name="delete_item" value="1">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../partials/footer.php'; ?>

