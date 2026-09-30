<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if (isset($_SESSION['user'])) {
	redirect('dashboard.php');
}

$error = '';
$username = '';
$phone = '+353';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$password = $_POST['password'] ?? '';

	if ($username === '' || $phone === '' || $password === '') {
		$error = 'Please fill in all fields.';
	} elseif (!str_starts_with($phone, '+353')) {
		$error = 'Phone number must start with +353.';
	} else {
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);

		if ($passwordHash === false) {
			$error = 'Unable to create your account.';
		} else {
			try {
				$stmt = $pdo->prepare(
					'INSERT INTO users (username, password_hash, phone)
					 VALUES (:username, :password_hash, :phone)'
				);
				$stmt->execute([
					'username' => $username,
					'password_hash' => $passwordHash,
					'phone' => $phone,
				]);

				$userId = (int) $pdo->lastInsertId();

				$twilio
					->verify
					->v2
					->services($_ENV['TWILIO_VERIFY_SID'])
					->verifications
					->create($phone, 'sms');

				$_SESSION['pending_user'] = [
					'id' => $userId,
					'username' => $username,
					'phone' => $phone,
				];

				redirect('verify.php');
			} catch (PDOException $e) {
				error_log($e->getMessage());
				$error = 'Unable to create account. The username may already be taken.';
			}
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Sign Up</title>
</head>
<body>

<h1>Sign Up</h1>

<?php if ($error): ?>
	<p><?= e($error) ?></p>
<?php endif; ?>

<form method="POST">
	<label for="username">Username</label><br>
	<input type="text" id="username" name="username" value="<?= e($username) ?>" required>

	<br><br>

	<label for="phone">Phone number</label><br>
	<input type="tel" id="phone" name="phone" value="<?= e($phone) ?>" placeholder="+353 ..." required>

	<br><br>

	<label for="password">Password</label><br>
	<input type="password" id="password" name="password" required>

	<br><br>

	<button type="submit">Create account</button>
</form>

<p><a href="login.php">Back to login</a></p>

</body>
</html>
