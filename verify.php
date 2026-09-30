<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if (!isset($_SESSION['pending_user'])) {
    redirect('login.php');
}

$error = '';

$pendingUser = $_SESSION['pending_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code'] ?? '');

    if ($code === '') {
        $error = 'Please enter your verification code.';
    } else {

        try {
            $verification = $twilio
                ->verify
                ->v2
                ->services($_ENV['TWILIO_VERIFY_SID'])
                ->verificationChecks
                ->create([
                    'to' => $pendingUser['phone'],
                    'code' => $code
                ]);

            if ($verification->status === 'approved') {

                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => $pendingUser['id'],
                    'username' => $pendingUser['username']
                ];

                unset($_SESSION['pending_user']);

                redirect('dashboard.php');

            } else {
                $error = 'Incorrect verification code.';
            }

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $error = 'Verification failed.';
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify MFA</title>
</head>
<body>

<h1>Two-Factor Authentication</h1>

<p>Enter the verification code sent to your phone.</p>

<?php if ($error): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>

<form method="POST">

    <label>Verification Code</label><br>

    <input
        type="text"
        name="code"
        inputmode="numeric"
        autocomplete="one-time-code"
        required
    >

    <br><br>

    <button type="submit">
        Verify
    </button>

</form>

</body>
</html>