<?php
$message = $_GET['message'] ?? "An error occurred. Please try again.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Failed</title>
    <link rel="stylesheet" href="fail.css">
</head>
<body>
    <div class="fail-container">
        <img src="fail.jpg" alt="Failure Icon" class="fail-image">
        <h1>Booking Failed</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="index.php" class="button">Return to Homepage</a>
    </div>
</body>
</html>
