<?php
session_start();
require_once 'storage.php';
require_once 'bookingstorage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$bookingsStorage = new BookingStorage();
$carsStorage = new Storage(new JsonIO('cars.json'));

$isAdmin = in_array('admin', $user['roles']);

if ($isAdmin) {
    $reservations = $bookingsStorage->findAll();
} else {
    $reservations = $bookingsStorage->findAll(['user_email' => $user['email']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <header>
        <h1>
            Logged in as:  
            <?= isset($user['fullname']) ? htmlspecialchars($user['fullname']) : 'Guest' ?>
        </h1>
    </header>
    <main>
        <section class="reservation-list">
            <h2><?= $isAdmin ? 'All Reservations' : 'Your Reservations' ?></h2>
            <?php if (empty($reservations)): ?>
                <p><?= $isAdmin ? 'No reservations found.' : 'You have no reservations yet.' ?></p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price (HUF)</th>
                            <?php if ($isAdmin): ?>
                                <th>User Email</th>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): 
                            $car = $carsStorage->findById($reservation['car_id']);
                            if ($car): 
                                $startDate = strtotime($reservation['start_date']);
                                $endDate = strtotime($reservation['end_date']);
                                $days = ($endDate - $startDate) / (60 * 60 * 24) + 1;
                                $totalPrice = $days * $car['daily_price_huf'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></td>
                                <td><?= htmlspecialchars($reservation['start_date']) ?></td>
                                <td><?= htmlspecialchars($reservation['end_date']) ?></td>
                                <td><?= number_format($totalPrice) ?> HUF</td>
                                <?php if ($isAdmin): ?>
                                    <td><?= htmlspecialchars($reservation['user_email']) ?></td>
                                    <td>
                                        <form action="deleteb.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($reservation['id']) ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this reservation?');">Delete</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endif; endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        <section class="logout">
            <form method="POST">
                <button type="submit" name="logout">Log Out</button>
            </form>
        </section>
        <section class="back-home">
            <a href="index.php" class="button">Back to Homepage</a>
        </section>
    </main>
</body>
</html>