<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if (isset($_SESSION['user'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash, phone
         FROM users
         WHERE username = :username'
    );

    $stmt->execute([
        'username' => $username
    ]);

    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user['password_hash'])
    ) {
        $error = 'Invalid username or password.';
    } else {

        $_SESSION['pending_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'phone' => $user['phone']
        ];
print_r($user);
        try {
            $twilio
                ->verify
                ->v2
                ->services($_ENV['TWILIO_VERIFY_SID'])
                ->verifications
                ->create(
                    $user['phone'],
                    'sms'
                );

            redirect('verify.php');

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $error = 'Unable to send verification code.';
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>MFA Login</title>
</head>
<body>

<h1>Login</h1>

<?php if ($error): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Username</label><br>
    <input type="text" name="username" required>

    <br><br>

    <label>Password</label><br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">
        Login
    </button>
    
    <br><br>

    <a href="sign-up.php">Sign Up</a>

</form>

</body>
</html>