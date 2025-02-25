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

function validate_car($post, &$data, &$errors) {
    $data = $post;

    if (empty($post['brand'])) {
        $errors['brand'] = "Brand is required.";
    }

    if (empty($post['model'])) {
        $errors['model'] = "Model is required.";
    }

    if (empty($post['year']) || !is_numeric($post['year']) || intval($post['year']) < 1886 || intval($post['year']) > date('Y')) {
        $errors['year'] = "Year must be a valid number between 1886 and the current year.";
    }

    if (empty($post['fuel_type']) || !in_array($post['fuel_type'], ['Petrol', 'Diesel', 'Electric', 'Hybrid'])) {
        $errors['fuel_type'] = "Fuel type must be one of: Petrol, Diesel, Electric, Hybrid.";
    }

    if (empty($post['transmission']) || !in_array($post['transmission'], ['Manual', 'Automatic'])) {
        $errors['transmission'] = "Transmission must be Manual or Automatic.";
    }

    if (empty($post['passengers']) || !is_numeric($post['passengers']) || intval($post['passengers']) <= 0) {
        $errors['passengers'] = "Passengers must be a positive number.";
    }

    if (empty($post['daily_price_huf']) || !is_numeric($post['daily_price_huf']) || intval($post['daily_price_huf']) <= 0) {
        $errors['daily_price_huf'] = "Daily price must be a positive number.";
    }

    if (empty($post['image'])) {
        $errors['image'] = "Image URL is required.";
    }

    return count($errors) === 0;
}

$errors = [];
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_car($_POST, $data, $errors)) {
        $carsStorage->add($data);
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
    <link rel="stylesheet" href="add.css">
    <title>Add Car</title>
</head>
<body>
    <form method="POST" novalidate>
        <h1>Add a New Car</h1>
        <label>Brand: 
            <input type="text" name="brand" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>">
        </label>
        <?php if (isset($errors['brand'])): ?>
            <div class="error-message"><?= $errors['brand'] ?></div>
        <?php endif; ?>

        <label>Model: 
            <input type="text" name="model" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
        </label>
        <?php if (isset($errors['model'])): ?>
            <div class="error-message"><?= $errors['model'] ?></div>
        <?php endif; ?>

        <label>Year: 
            <input type="text" name="year" value="<?= htmlspecialchars($_POST['year'] ?? '') ?>">
        </label>
        <?php if (isset($errors['year'])): ?>
            <div class="error-message"><?= $errors['year'] ?></div>
        <?php endif; ?>

        <label>Fuel Type: 
            <select name="fuel_type">
                <option value="">Select...</option>
                <option value="Petrol" <?= isset($data['fuel_type']) && $data['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                <option value="Diesel" <?= isset($data['fuel_type']) && $data['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                <option value="Electric" <?= isset($data['fuel_type']) && $data['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                <option value="Hybrid" <?= isset($data['fuel_type']) && $data['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
            </select>
        </label>
        <?php if (isset($errors['fuel_type'])): ?>
            <div class="error-message"><?= $errors['fuel_type'] ?></div>
        <?php endif; ?>

        <label>Transmission: 
            <select name="transmission">
                <option value="">Select...</option>
                <option value="Manual" <?= isset($data['transmission']) && $data['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                <option value="Automatic" <?= isset($data['transmission']) && $data['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
            </select>
        </label>
        <?php if (isset($errors['transmission'])): ?>
            <div class="error-message"><?= $errors['transmission'] ?></div>
        <?php endif; ?>

        <label>Passengers: 
            <input type="text" name="passengers" value="<?= htmlspecialchars($_POST['passengers'] ?? '') ?>">
        </label>
        <?php if (isset($errors['passengers'])): ?>
            <div class="error-message"><?= $errors['passengers'] ?></div>
        <?php endif; ?>

        <label>Price per Day (HUF): 
            <input type="text" name="daily_price_huf" value="<?= htmlspecialchars($_POST['daily_price_huf'] ?? '') ?>">
        </label>
        <?php if (isset($errors['daily_price_huf'])): ?>
            <div class="error-message"><?= $errors['daily_price_huf'] ?></div>
        <?php endif; ?>

        <label>Image URL: 
            <input type="text" name="image" value="<?= htmlspecialchars($_POST['image'] ?? '') ?>">
        </label>
        <?php if (isset($errors['image'])): ?>
            <div class="error-message"><?= $errors['image'] ?></div>
        <?php endif; ?>

        <button type="submit">Add Car</button>
    </form>
</body>
</html>