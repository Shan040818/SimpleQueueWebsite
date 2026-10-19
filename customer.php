
<?php
require_once "db.php";

$message = "";
$queueNumber = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["customer_name"] ?? "");

    if ($name === "" || strlen($name) > 100) {
        $message = "Please enter a valid name.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO customers (customer_name) VALUES (?)"
            );
            $stmt->execute([$name]);

            $customerId = $pdo->lastInsertId();

            $stmt = $pdo->query(
                "SELECT MAX(CAST(queue_number AS UNSIGNED))
                 FROM queue FOR UPDATE"
            );
            $lastNumber = (int) $stmt->fetchColumn();
            $nextNumber = $lastNumber + 1;

            $queueNumber = str_pad(
                (string) $nextNumber, 3, "0", STR_PAD_LEFT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO queue (customer_id, queue_number)
                 VALUES (?, ?)"
            );
            $stmt->execute([$customerId, $queueNumber]);

            $pdo->commit();
            $message = "Queue number generated successfully!";
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Queue Number</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        .card {
            background: white;
            max-width: 420px;
            margin: 60px auto;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        input, button {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin-top: 10px;
        }
        button {
            background: #2563eb;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .ticket {
            background: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Get Your Queue Number</h1>
        <p>Enter your name to join the queue.</p>

        <?php if ($message !== ""): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($queueNumber !== ""): ?>
            <div class="ticket">
                <p>Your queue number is</p>
                <h1><?= htmlspecialchars($queueNumber) ?></h1>
                <p>Please wait for your turn.</p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="customer_name"
                   maxlength="100" placeholder="Enter your full name"
                   required>
            <button type="submit">Get Queue Number</button>
        </form>
    </div>
</body>
</html>
