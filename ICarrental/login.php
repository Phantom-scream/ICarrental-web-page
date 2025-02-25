<?php
session_start();
require_once("userstorage.php");
require_once("auth.php");

$user_storage = new UserStorage();
$auth = new Auth($user_storage);

$data = [];
$errors = [];

function validate_login($post, &$data, &$errors) {
    $data['email'] = trim($post['email'] ?? '');
    $data['password'] = $post['password'] ?? '';

    if (empty($data['email'])) {
        $errors['email'] = "Email is required.";
    }elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($data['password'])) {
        $errors['password'] = "Password is required.";
    }

    return count($errors) === 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (validate_login($_POST, $data, $errors)) {
        $user = $auth->authenticate($data['email'], $data['password']);
        if ($user) {
            $auth->login($user);
            header("Location: index.php");
            exit;
        } else {
            $errors['authentication'] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <header>
        <h1>Login to iKarRental</h1>
    </header>
    <main>
        <section class="auth-form">
            <form method="POST" action="login.php" novalidate>
                <?php if (isset($errors['authentication'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['authentication']) ?></div>
                <?php endif; ?>

                <label for="email">Email Address:</label>
                <input id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="feedback"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>

                <button type="submit">Login</button>
            </form>
            <div class="back-home">
                <a href="index.php" class="button">Back to Homepage</a>
            </div>
        </section>
    </main>
</body>
</html>
