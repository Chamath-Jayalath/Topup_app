<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $amount = (float)$_POST['amount']; // Cast to float for accuracy

        try {
            // Use a transaction to ensure data integrity
            $conn->begin_transaction();

            $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id = ?");
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("di", $amount, $_SESSION['user_id']); // "d" for decimal, "i" for integer
            if ($stmt->execute() === false) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $conn->commit(); // Commit the transaction if everything is successful
            $success_message = "Funds added successfully!";
        } catch (Exception $e) {
            $conn->rollback(); // Rollback in case of error
            error_log($e->getMessage());
            $error_message = "An error occurred. Please try again later.";
        }
    } else {
        $error_message = "Please enter a valid amount.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Up</title>
</head>
<body>
    <h2>Top Up Your Balance</h2>
    <?php if ($success_message !== "") { echo "<p style='color: green;'>$success_message</p>"; } ?>
    <?php if ($error_message !== "") { echo "<p style='color: red;'>$error_message</p>"; } ?>
    <form method="post">
        Amount: <input type="number" name="amount" step="0.01" min="0.01" required><br>
        <button type="submit">Add Funds</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>