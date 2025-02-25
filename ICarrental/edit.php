<?php
require 'storage.php';
require 'auth.php';

session_start();
$carsStorage = new Storage(new JsonIO('cars.json'));
$userStorage = new Storage(new JsonIO('users.json'));
$auth = new Auth($userStorage);

$user = $auth->authenticated_user();

if (!$user || !in_array('admin', $user['roles'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || !$car = $carsStorage->findById($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$data = $car;
$errors = [];

function validate_car($data, &$errors) {
    if (empty($data['brand'])) {
        $errors['brand'] = "Brand is required.";
    }
    if (empty($data['model'])) {
        $errors['model'] = "Model is required.";
    }
    if (!in_array($data['transmission'], ['Manual', 'Automatic'])) {
        $errors['transmission'] = "Please select a valid transmission type.";
    }
    if ($data['passengers'] <= 0) {
        $errors['passengers'] = "Passengers must be a positive number.";
    }
    if ($data['daily_price_huf'] <= 0) {
        $errors['daily_price_huf'] = "Price must be a positive number.";
    }
    if (empty($data['image'])) {
        $errors['image'] = "Image URL is required.";
    }
    if ($data['year'] <= 0 || strlen((string)$data['year']) !== 4) {
        $errors['year'] = "Please provide a valid year.";
    }
    if (!in_array($data['fuel_type'], ['Petrol', 'Diesel', 'Electric', 'Hybrid'])) {
        $errors['fuel_type'] = "Please select a valid fuel type.";
    }
    return count($errors) === 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['brand'] = trim($_POST['brand'] ?? '');
    $data['model'] = trim($_POST['model'] ?? '');
    $data['transmission'] = $_POST['transmission'] ?? '';
    $data['passengers'] = intval($_POST['passengers'] ?? 0);
    $data['daily_price_huf'] = intval($_POST['daily_price_huf'] ?? 0);
    $data['image'] = trim($_POST['image'] ?? '');
    $data['year'] = intval($_POST['year'] ?? 0);
    $data['fuel_type'] = $_POST['fuel_type'] ?? '';

    if (validate_car($data, $errors)) {
        $carsStorage->update($data['id'], $data);
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit.css">
    <title>Edit Car</title>
</head>
<body>
    <form method="POST" novalidate>
        <h1>Edit Car</h1>
        <label>Brand:
            <input type="text" name="brand" value="<?= htmlspecialchars($data['brand']) ?>">
            <?php if (isset($errors['brand'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['brand']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Model:
            <input type="text" name="model" value="<?= htmlspecialchars($data['model']) ?>">
            <?php if (isset($errors['model'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['model']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Transmission:
            <select name="transmission">
                <option value="Manual" <?= $data['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                <option value="Automatic" <?= $data['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
            </select>
            <?php if (isset($errors['transmission'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['transmission']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Passengers:
            <input type="number" name="passengers" value="<?= htmlspecialchars($data['passengers']) ?>">
            <?php if (isset($errors['passengers'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['passengers']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Price per Day (HUF):
            <input type="number" name="daily_price_huf" value="<?= htmlspecialchars($data['daily_price_huf']) ?>">
            <?php if (isset($errors['daily_price_huf'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['daily_price_huf']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Image URL:
            <input type="text" name="image" value="<?= htmlspecialchars($data['image']) ?>">
            <?php if (isset($errors['image'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['image']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Year:
            <input type="number" name="year" value="<?= htmlspecialchars($data['year']) ?>">
            <?php if (isset($errors['year'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['year']) ?></div>
            <?php endif; ?>
        </label>
        
        <label>Fuel Type:
            <select name="fuel_type">
                <option value="Petrol" <?= $data['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                <option value="Diesel" <?= $data['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                <option value="Electric" <?= $data['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                <option value="Hybrid" <?= $data['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
            </select>
            <?php if (isset($errors['fuel_type'])): ?>
                <div class="feedback"><?= htmlspecialchars($errors['fuel_type']) ?></div>
            <?php endif; ?>
        </label>
        
        <button type="submit">Save Changes</button>
        <div class="back-home">
            <a href="index.php" class="button">Back to Homepage</a>
        </div>
    </form>
</body>
</html>