<?php
session_start();
require_once("userstorage.php");
require_once("auth.php");

$user_storage = new UserStorage();
$auth = new Auth($user_storage);

$data = [];
$errors = [];

function validate_registration($post, &$data, &$errors) {
    $data['fullname'] = trim($post['fullname'] ?? '');
    $data['email'] = trim($post['email'] ?? '');
    $data['password'] = $post['password'] ?? '';

    if (empty($data['fullname'])) {
        $errors['fullname'] = "Full Name is required.";
    }

    if (empty($data['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    } elseif ($data['email'] === 'admin@ikarrental.hu') {
        $errors['email'] = "This email is reserved for the administrator.";
    }

    if (empty($data['password'])) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($data['password']) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }

    return count($errors) === 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (validate_registration($_POST, $data, $errors)) {
        if ($auth->user_exists($data['email'])) {
            $errors['email'] = "Email is already registered.";
        } else {
            $auth->register([
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'password' => $data['password'],
                'roles' => ['user'],
            ]);
            $success_message = "Registration successful! You can now log in.";
            $data = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <header>
        <h1>Register for iKarRental</h1>
    </header>
    <main>
        <section class="auth-form">
            <?php if (!empty($success_message)): ?>
                <div class="success-alert">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="register.php" novalidate>
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($data['fullname'] ?? '') ?>">
                <?php if (isset($errors['fullname'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['fullname']) ?></div>
                <?php endif; ?>

                <label for="email">Email Address:</label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>

                <button type="submit">Register</button>
            </form>
            <div class="back-home">
                <a href="index.php" class="button">Back to Homepage</a>
            </div>
        </section>
    </main>
</body>
</html>