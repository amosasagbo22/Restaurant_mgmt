<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$pdo = get_pdo();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$menu_stmt = $pdo->query('SELECT m.id, m.name, m.description, m.price, c.name AS category
                           FROM menu_items m JOIN categories c ON c.id = m.category_id
                           ORDER BY c.name, m.name');
$menu_items = $menu_stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>
<h2>Menu</h2>
<?php foreach ($categories as $cat): ?>
    <h3><?php echo h($cat['name']); ?></h3>
    <div class="grid">
    <?php foreach ($menu_items as $item): if ($item['category'] !== $cat['name']) continue; ?>
        <div class="card">
            <div class="placeholder-img"><?php echo h($item['name']); ?></div>
            <div class="content">
                <h4><?php echo h($item['name']); ?></h4>
                <p><?php echo h($item['description']); ?></p>
                <div class="price"><?php echo format_currency((float)$item['price']); ?></div>
                <form method="post" action="cart.php">
                    <input type="hidden" name="add_id" value="<?php echo (int)$item['id']; ?>">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endforeach; ?>
<?php include __DIR__ . '/partials/footer.php'; ?>

