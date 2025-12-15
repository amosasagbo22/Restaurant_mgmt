<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';
require_login();

$pdo = get_pdo();

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_reservation'])) {
        $id = (int)post('id');
        $stmt = $pdo->prepare('DELETE FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Reservation deleted';
    }
}

// Get all reservations
$reservations = $pdo->query('
    SELECT * FROM reservations 
    ORDER BY reservation_datetime ASC
')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<h2>Reservation Management</h2>
<p><a class="btn" href="dashboard.php">Back to Dashboard</a></p>

<?php if (!empty($message)): ?>
    <div class="alert success"><?php echo h($message); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Reservations</h3>
        <div class="stat-number"><?php echo count($reservations); ?></div>
    </div>
    <div class="stat-card">
        <h3>Today's Reservations</h3>
        <div class="stat-number"><?php echo count(array_filter($reservations, function($r) { return $r['reservation_datetime'] === date('Y-m-d'); })); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Guests</h3>
        <div class="stat-number"><?php echo array_sum(array_column($reservations, 'party_size')); ?></div>
    </div>
</div>

<h3>All Reservations</h3>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Date</th>
            <th>Time</th>
            <th>Party Size</th>
            <th>Special Requests</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservations as $res): ?>
            <tr class="<?php echo $res['reservation_datetime'] === date('Y-m-d') ? 'today' : ''; ?>">
                <td>#<?php echo (int)$res['id']; ?></td>
                <td><?php echo h($res['name']); ?></td>
                <td><?php echo h($res['phone']); ?></td>
                <td><?php echo h($res['email'] ?: '-'); ?></td>
                <td><?php echo date('M j, Y', strtotime($res['reservation_datetime'])); ?></td>
                <td><?php echo (int)$res['party_size']; ?> people</td>
                <td><?php echo h($res['special_requests'] ?: '-'); ?></td>
                <td>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this reservation?');">
                        <input type="hidden" name="id" value="<?php echo (int)$res['id']; ?>">
                        <button type="submit" name="delete_reservation" value="1" class="btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../partials/footer.php'; ?> 