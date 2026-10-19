
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$stmt = $pdo->query("
    SELECT
        q.queue_id,
        q.queue_number,
        q.status,
        q.created_at,
        c.customer_name
    FROM queue q
    JOIN customers c ON q.customer_id = c.customer_id
    ORDER BY q.queue_id DESC
");

$queues = $stmt->fetchAll(PDO::FETCH_ASSOC);

$waiting = 0;
$served = 0;
$cancelled = 0;
$nowServing = $pdo->query("
    SELECT queue_number
    FROM queue
    WHERE status = 'Served'
    ORDER BY queue_id DESC
    LIMIT 1
")->fetchColumn();

foreach ($queues as $q) {
    if ($q["status"] === "Waiting") $waiting++;
    if ($q["status"] === "Served") $served++;
    if ($q["status"] === "Cancelled") $cancelled++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Queue System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 25px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        .header, .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 2px 8px #00000010;
        }
        .stats .card {
            flex: 1;
            min-width: 120px;
        }
        button, .logout {
            background: #2563eb;
            color: white;
            border: 0;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout { background: #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .table-wrap { overflow-x: auto; }
     </style>
     <meta http-equiv="refresh" content="3">
</head>
<body>
<div class="container">
    <div class="header">    
        <div>
            <h1>Simple Queue System</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION["admin_username"]) ?>!</p>
        </div>
        <a class="logout" href="logout.php">Logout</a>
    </div>
    <div class="card" style="text-align: center; background: #dcfce7;">
    <h2>NOW SERVING</h2>

    <h1 style="font-size: 60px; color: #16a34a; margin: 10px 0;">
        <?= $nowServing !== false ? htmlspecialchars($nowServing) : "---" ?>
    </h1>

    <p>
        <?= $nowServing !== false
            ? "Please proceed to the counter."
            : "No customer is being served yet." ?>
    </p>
</div>
    <div class="stats">
        <div class="card">
            <h3>Waiting</h3>
            <h2><?= $waiting ?></h2>
        </div>
        <div class="card">
            <h3>Served</h3>
            <h2><?= $served ?></h2>
        </div>
        <div class="card">
            <h3>Cancelled</h3>
            <h2><?= $cancelled ?></h2>
        </div>
    </div>

    <div class="card">
        <h2>Queue List</h2>
        
      <form method="POST" action="actions.php"
        onsubmit="return confirm('Call the next waiting customer?');">
       <input type="hidden" name="action" value="next">
       <button type="submit">Call Next Customer</button>
     </form>
     <br>
 
        <p>Wala pang customers sa queue. Magdadagdag tayo ng customer form sa susunod.</p>

        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Queue No.</th>
                    <th>Customer Name</th>
                    <th>Status</th>
                    <th>Date &amp; Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$queues): ?>
                <tr>
                    <td colspan="4">No queue records yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($queues as $q): ?>
                    <tr>
                        <td><?= htmlspecialchars($q["queue_number"]) ?></td>
                        <td><?= htmlspecialchars($q["customer_name"]) ?></td>
                        <td><?= htmlspecialchars($q["status"]) ?></td>
                        <td><?= htmlspecialchars($q["created_at"]) ?></td>
                        
                        <td>
                    <?php if ($q["status"] === "Waiting"): ?>
                     <form method="POST" action="actions.php"
                      onsubmit="return confirm('Cancel this queue?');">
                     <input type="hidden" name="action" value="cancel">
                     <input type="hidden" name="queue_id"
                      value="<?= (int) $q['queue_id'] ?>">
                    <button type="submit">Cancel</button>
                  </form>
                 <?php else: ?>
                -
             <?php endif; ?>
             </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>
