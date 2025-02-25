<?php
require_once 'storage.php';

$carId = $_GET['car_id'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$totalPrice = $_GET['total_price'] ?? null;

$carsStorage = new Storage(new JsonIO('cars.json'));
$car = $carsStorage->findOne(['id' => (int)$carId]);

if (!$car || !$startDate || !$endDate || !$totalPrice) {
    header('Location: fail.php?message=' . urlencode("Invalid booking details."));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful</title>
    <link rel="stylesheet" href="success.css">
</head>
<body>
    <div class="success-container">
        <img src="tick.jpg" alt="Success" class="tick">
        <h1>Booking Successful!</h1>
        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" class="car-image">
        <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($startDate) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($endDate) ?></p>
        <p><strong>Total Price:</strong> <?= number_format($totalPrice) ?> HUF</p>
        <a href="profile.php" class="button">Go to Profile</a>
        <a href="index.php" class="button">Return to Homepage</a>
    </div>
</body>
</html>
