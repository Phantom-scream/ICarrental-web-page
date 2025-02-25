<?php
session_start();
require 'storage.php';
require 'auth.php';

$carsStorage = new Storage(new JsonIO('cars.json'));
$auth = new Auth(new Storage(new JsonIO('users.json')));

$carId = $_GET['id'] ?? null;

if ($carId === null) {
    header('Location: index.php?error=InvalidCarId');
    exit;
}

$car = $carsStorage->findById($carId);

if ($car === null) {
    header('Location: index.php?error=CarNotFound');
    exit;
}

$isAuthenticated = $auth->is_authenticated();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details - <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></title>
    <link rel="stylesheet" href="cardetails.css">
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h1>
    </header>

    <main>
        <div class="car-details">
            <img 
                src="<?= htmlspecialchars($car['image']) ?>" 
                alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> image"
            >
            <table>
                <thead>
                    <tr>
                        <th>Attribute</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        'Brand' => $car['brand'],
                        'Model' => $car['model'],
                        'Year' => $car['year'],
                        'Transmission' => $car['transmission'],
                        'Fuel Type' => $car['fuel_type'],
                        'Passengers' => $car['passengers'],
                        'Daily Price (HUF)' => number_format($car['daily_price_huf']) . ' HUF'
                    ] as $attribute => $value): ?>
                        <tr>
                            <td><?= htmlspecialchars($attribute) ?></td>
                            <td><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <form action="booking.php" method="GET">
                <input type="hidden" name="id" value="<?= htmlspecialchars($carId) ?>">
                <button type="submit" class="book-button">Select a Date and Book It</button>
            </form>
        </div>

        <div class="back-home">
            <a href="index.php" class="back-link">← Back to Homepage</a>
        </div>
    </main>

    <script>
        const form = document.querySelector('.actions form');
        form.addEventListener('submit', (event) => {
            <?php if (!$isAuthenticated): ?>
            event.preventDefault();
            window.location.href = 'login.php';
            <?php endif; ?>
        });
    </script>
</body>
</html>
