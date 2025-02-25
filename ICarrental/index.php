<?php
require 'storage.php';
require 'auth.php';

session_start();
$carsStorage = new Storage(new JsonIO('cars.json'));
$bookingsStorage = new Storage(new JsonIO('booking.json'));
$userStorage = new Storage(new JsonIO('users.json'));
$auth = new Auth($userStorage);

$user = $auth->authenticated_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $auth->logout();
    header("Location: index.php");
    exit();
}

$cars = $carsStorage->findMany(function ($car) use ($bookingsStorage) {
    if (!empty($_GET['transmission']) && $car['transmission'] !== $_GET['transmission']) {
        return false;
    }

    if (!empty($_GET['passengers']) && $car['passengers'] < intval($_GET['passengers'])) {
        return false;
    }

    if (!empty($_GET['price_min']) && !empty($_GET['price_max'])) {
        $price = $car['daily_price_huf'];
        if ($price < intval($_GET['price_min']) || $price > intval($_GET['price_max'])) {
            return false;
        }
    }

    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $startDate = strtotime($_GET['start_date']);
        $endDate = strtotime($_GET['end_date']);

        $bookings = $bookingsStorage->findMany(function ($booking) use ($car) {
            return $booking['car_id'] === $car['id'];
        });

        foreach ($bookings as $booking) {
            $bookingStart = strtotime($booking['start_date']);
            $bookingEnd = strtotime($booking['end_date']);
            
            if (($startDate <= $bookingEnd) && ($endDate >= $bookingStart)) {
                return false; 
            }
        }
    }

    return true;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental Homepage</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1>iKarRental: Browse Available Cars</h1>
            <div class="auth-buttons">
                <?php if ($user): ?>
                    <div class="user-info">
                        <p>Welcome, <?= htmlspecialchars($user['fullname']) ?>!</p>
                        <a href="profile.php" class="auth-button profile-button">Profile</a>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="logout" class="auth-button logout-button">Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="auth-button">Login</a>
                    <a href="register.php" class="auth-button">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?php if ($user && in_array('admin', $user['roles'])): ?>
            <section class="admin-options">
                <a href="add.php" class="auth-button add-button">Add New Car</a>
            </section>
        <?php endif; ?>

        <section class="filters">
            <form method="GET" action="index.php">
                <label for="transmission">Transmission:</label>
                <select name="transmission" id="transmission">
                    <option value="">All</option>
                    <option value="Automatic" <?= isset($_GET['transmission']) && $_GET['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual" <?= isset($_GET['transmission']) && $_GET['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                </select>

                <label for="passengers">Passengers:</label>
                <input type="number" name="passengers" id="passengers" value="<?= htmlspecialchars($_GET['passengers'] ?? '') ?>" placeholder="Min passengers">

                <label for="price_min">Min Price (HUF):</label>
                <input type="number" name="price_min" id="price_min" value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>" placeholder="Min HUF">

                <label for="price_max">Max Price (HUF):</label>
                <input type="number" name="price_max" id="price_max" value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>" placeholder="Max HUF">

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">

                <button type="submit">Apply Filters</button>
            </form>
        </section>

        <section class="car-list">
            <?php if (count($cars) > 0): ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> image">
                        <h3><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h3>
                        <p><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission']) ?></p>
                        <p><strong>Passengers:</strong> <?= htmlspecialchars($car['passengers']) ?></p>
                        <p><strong>Price:</strong> <?= htmlspecialchars($car['daily_price_huf']) ?> HUF/day</p>
                        <a href="cardetails.php?id=<?= htmlspecialchars($car['id']) ?>" class="details-link">Book</a>

                        <?php if ($user && in_array('admin', $user['roles'])): ?>
                            <div class="admin-actions">
                                <a href="edit.php?id=<?= htmlspecialchars($car['id']) ?>" class="edit-button">Edit</a>
                                <a href="delete.php?id=<?= htmlspecialchars($car['id']) ?>" class="delete-button">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No cars available based on the selected criteria.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>