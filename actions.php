
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$action = $_POST["action"] ?? "";

try {
    if ($action === "next") {
        $pdo->beginTransaction();

        $stmt = $pdo->query(
            "SELECT queue_id
             FROM queue
             WHERE status = 'Waiting'
             ORDER BY queue_id ASC
             LIMIT 1
             FOR UPDATE"
        );

        $next = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($next) {
            $update = $pdo->prepare(
                "UPDATE queue
                 SET status = 'Served'
                 WHERE queue_id = ?"
            );
            $update->execute([$next["queue_id"]]);
        }

        $pdo->commit();

    } elseif ($action === "cancel") {
        $queueId = filter_input(
            INPUT_POST,
            "queue_id",
            FILTER_VALIDATE_INT
        );

        if ($queueId) {
            $stmt = $pdo->prepare(
                "UPDATE queue
                 SET status = 'Cancelled'
                 WHERE queue_id = ?
                   AND status = 'Waiting'"
            );
            $stmt->execute([$queueId]);
        }
    }

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    exit("Something went wrong while updating the queue.");
}

header("Location: index.php");
exit;
