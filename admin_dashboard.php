<?php
// admin_dashboard.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$query = "SELECT r.id, u.username, r.file_name, r.status, r.amount, u.balance, u.id as user_id FROM receipts r JOIN users u ON r.user_id = u.id WHERE r.status = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$status = 'Pending';
$stmt->bind_param('s', $status);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$receipts = [];
while ($row = $result->fetch_assoc()) {
    $receipts[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        img {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 2px;
        }

        .btn {
            margin: 5px;
        }

        .float-end {
            float: right;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }
        #receipts-table{
            table-layout: fixed;
            width: 100%;
        }
        #receipts-table td{
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <a href="logout.php" class="btn btn-danger float-end">Logout</a>

        <h3>Pending Receipts</h3>
        <?php if (!empty($receipts)): ?>
            <table class="table table-bordered mt-4" id="receipts-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Receipt</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $receipt): ?>
                        <tr id="receipt-<?php echo $receipt['id']; ?>">
                            <td><?php echo htmlspecialchars($receipt['username']); ?></td>
                            <td><a href="uploads/<?php echo htmlspecialchars($receipt['file_name']); ?>" target="_blank"><img src="uploads/<?php echo htmlspecialchars($receipt['file_name']); ?>" alt="Receipt"></a></td>
                            <td id="amount-<?php echo $receipt['id']; ?>">LKR <?php echo number_format($receipt['amount'], 2); ?></td>
                            <td id="balance-<?php echo $receipt['id']; ?>"><?php echo number_format($receipt['balance'], 2); ?></td>
                            <td id="status-<?php echo $receipt['id']; ?>"><?php echo htmlspecialchars($receipt['status']); ?></td>
                            <td>
                                <button class="btn btn-success" onclick="approveReceipt(<?php echo $receipt['id']; ?>, <?php echo $receipt['user_id']; ?>)">Approve</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending receipts found.</p>
        <?php endif; ?>
    </div>

    <script>
        function approveReceipt(receiptId, userId) {
            fetch('approve_receipt.php?receipt_id=' + receiptId + '&user_id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('status-' + receiptId).textContent = 'Approved';
                        document.getElementById('balance-' + receiptId).textContent = data.updated_balance;
                        document.getElementById('receipt-' + receiptId).remove();
                    } else {
                        alert('Error: ' + data.message);
                        console.error("Server Error:", data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('A network error occurred. Please try again.');
                });
        }
    </script>
</body>
</html>