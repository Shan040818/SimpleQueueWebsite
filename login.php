
<?php
session_start();
require_once "db.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT admin_id, username, password
         FROM admins
         WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin["password"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["admin_id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card login-card">
            <h1>Admin Login</h1>
            <p>Sign in to manage the queue system.</p>

            <?php if ($error !== ""): ?>
                <p class="login-error">
                    <?= htmlspecialchars($error) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       autocomplete="username" required>

                <br><br>

                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>

                <br><br>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
