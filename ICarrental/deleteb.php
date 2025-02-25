<?php
require 'storage.php';
require 'auth.php';

session_start();

$bookingsStorage = new Storage(new JsonIO('booking.json'));
$userStorage = new Storage(new JsonIO('users.json'));
$auth = new Auth($userStorage);

$user = $auth->authenticated_user();
if (!$user || !in_array('admin', $user['roles'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_POST['id'])) {
    header("Location: profile.php");
    exit();
}

$booking = $bookingsStorage->findById($_POST['id']);
if (!$booking) {
    header("Location: profile.php");
    exit();
}

$bookingsStorage->delete($_POST['id']);

header("Location: profile.php");
exit();
?>