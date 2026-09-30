<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if (!isset($_SESSION['user'])) {
    redirect('login.php');
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['logout'])
) {
    $_SESSION = [];

    session_destroy();

    redirect('login.php');
}

$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h1>
    Welcome <?= e($user['username']) ?>
</h1>

<p>
    You successfully logged in using password authentication and Twilio MFA.
</p>

<form method="POST">
    <button type="submit" name="logout">
        Logout
    </button>
</form>

</body>
</html>