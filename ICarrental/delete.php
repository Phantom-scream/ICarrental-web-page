<?php
require 'storage.php';
require 'auth.php';

session_start();

$carsStorage = new Storage(new JsonIO('cars.json'));
$userStorage = new Storage(new JsonIO('users.json'));
$auth = new Auth($userStorage);

$user = $auth->authenticated_user();
if (!$user || !in_array('admin', $user['roles'])) {
    die("Access denied. Only administrators can delete cars.");
}

if (!isset($_GET['id'])) {
    die("No car ID provided.");
}

$carId = $_GET['id'];

$car = $carsStorage->findById($carId);
if (!$car) {
    die("Car not found.");
}

$carsStorage->delete($carId);

header("Location: index.php");
exit();
?>