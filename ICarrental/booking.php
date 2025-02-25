<?php
session_start();
require_once 'storage.php';
require_once 'bookingstorage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$bookingsStorage = new BookingStorage();
$carsStorage = new Storage(new JsonIO('cars.json'));

$carId = $_POST['car_id'] ?? $_GET['id'] ?? null;

if ($carId === null) {
    header('Location: fail.php?message=' . urlencode("Invalid car ID. Please try again."));
    exit;
}

$car = $carsStorage->findOne(['id' => (int)$carId]);

if ($car === null) {
    header('Location: fail.php?message=' . urlencode("Car not found. Please try again."));
    exit;
}

$bookings = $bookingsStorage->findAll(['car_id' => (int)$carId]);
$unavailableDates = [];

foreach ($bookings as $booking) {
    $currentDate = strtotime($booking['start_date']);
    $endDate = strtotime($booking['end_date']);
    while ($currentDate <= $endDate) {
        $unavailableDates[] = date('Y-m-d', $currentDate);
        $currentDate = strtotime('+1 day', $currentDate);
    }
}

$unavailableDatesJson = json_encode($unavailableDates);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    if ($startDate === null || $endDate === null) {
        header('Location: fail.php?message=' . urlencode("Both start and end dates are required."));
        exit;
    }

    $desiredStart = strtotime($startDate);
    $desiredEnd = strtotime($endDate);

    if ($desiredStart > $desiredEnd) {
        header('Location: fail.php?message=' . urlencode("The start date must be earlier than or equal to the end date."));
        exit;
    }

    $overlappingBookings = $bookingsStorage->findAll(['car_id' => (int)$carId]);

    $isOverlap = false;
    foreach ($overlappingBookings as $booking) {
        $existingStart = strtotime($booking['start_date']);
        $existingEnd = strtotime($booking['end_date']);

        if ($desiredStart <= $existingEnd && $desiredEnd >= $existingStart) {
            $isOverlap = true;
            break;
        }
    }

    if ($isOverlap) {
        $carName = htmlspecialchars($car['brand'] . ' ' . $car['model']);
        header('Location: fail.php?message=' . urlencode("The $carName is not available in the specified interval from $startDate to $endDate. Try entering a different interval or search for another vehicle."));
        exit;
    } else {
        $user = $_SESSION['user'];
        $days = ($desiredEnd - $desiredStart) / (60 * 60 * 24) + 1;
        $totalPrice = $days * $car['daily_price_huf'];

        $booking = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_email' => $user['email'],
            'car_id' => (int)$carId,
        ];
        $bookingsStorage->add($booking);

        header('Location: success.php?car_id=' . urlencode($carId) .
            '&start_date=' . urlencode($startDate) .
            '&end_date=' . urlencode($endDate) .
            '&total_price=' . urlencode($totalPrice));
        exit;
    }
}

function renderForm($carId, $unavailableDatesJson) {
    return <<<HTML
    <form method="POST" action="booking.php" class="booking-form">
        <input type="hidden" name="car_id" value="{$carId}">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <button type="submit" name="action" value="book">Book</button>
    </form>
    <script>
        const unavailableDates = {$unavailableDatesJson};
        const startDateInput = document.querySelector('#start_date');
        const endDateInput = document.querySelector('#end_date');

        function disableUnavailableDates(input) {
            input.addEventListener('input', () => {
                const selectedDate = input.value;
                if (unavailableDates.includes(selectedDate)) {
                    alert('Selected date is unavailable. Please choose another date.');
                    input.value = '';
                }
            });
        }

        disableUnavailableDates(startDateInput);
        disableUnavailableDates(endDateInput);
    </script>
    HTML;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link rel="stylesheet" href="booking.css">
</head>
<body>
    <header>
        <h1>Booking</h1>
    </header>
    <main>
        <div class="car-info">
            <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
            <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
        </div>
        <?= renderForm($carId, $unavailableDatesJson) ?>
    </main>
</body>
</html>