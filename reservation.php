<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$pdo = get_pdo();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name', ''));
    $email = trim(post('email', ''));
    $phone = trim(post('phone', ''));
    $datetime = trim(post('datetime', ''));
    $party = (int)post('party', 1);
    $requests = trim(post('requests', ''));

    if (!$name || !$phone || !$datetime || $party < 1) {
        $error = 'Please fill all required fields correctly.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO reservations (name, email, phone, reservation_datetime, party_size, special_requests) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$name, $email, $phone, $datetime, $party, $requests]);
        $success = 'Reservation booked! We look forward to seeing you.';
    }
}

include __DIR__ . '/partials/header.php';
?>

<h2>Reservation</h2>
<?php if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success"><?php echo h($success); ?></div><?php endif; ?>

<form method="post">
    <div class="row">
        <div>
            <label>Name
                <input type="text" name="name" required>
            </label>
        </div>
        <div>
            <label>Email
                <input type="email" name="email">
            </label>
        </div>
    </div>
    <div class="row">
        <div>
            <label>Phone
                <input type="text" name="phone" required>
            </label>
        </div>
        <div>
            <label>Date & Time
                <input type="datetime-local" name="datetime" required>
            </label>
        </div>
    </div>
    <div class="row">
        <div>
            <label>Party Size
                <input type="number" name="party" min="1" value="2" required>
            </label>
        </div>
        <div>
        </div>
    </div>
    <div class="row full">
        <div>
            <label>Special Requests
                <textarea name="requests" rows="4"></textarea>
            </label>
        </div>
    </div>
    <p><button type="submit">Book Reservation</button></p>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>

