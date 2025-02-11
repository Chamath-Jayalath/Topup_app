<?php
// approve_receipt.php
require 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['receipt_id']) || !isset($_GET['user_id']) || !is_numeric($_GET['receipt_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters. Missing or invalid receipt_id or user_id']);
    exit;
}

$receipt_id = (int)$_GET['receipt_id'];
$user_id = (int)$_GET['user_id'];

$conn->begin_transaction();

try {
    // Fetch the receipt amount
    $stmt = $conn->prepare("SELECT amount FROM receipts WHERE id = ?");
    $stmt->bind_param("i", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Receipt not found.");
    }
    $row = $result->fetch_assoc();
    $amount = $row['amount'];
    $stmt->close();

    // Update receipt status
    $stmt = $conn->prepare("UPDATE receipts SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param('i', $receipt_id);
    if (!$stmt->execute()) {
        throw new Exception("Update receipt status failed: " . $stmt->error);
    }
    $stmt->close();

    // Update user balance
    $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
    $balance_stmt = $conn->prepare($update_balance_query);
    $balance_stmt->bind_param('di', $amount, $user_id);
    if (!$balance_stmt->execute()) {
        throw new Exception("Balance Update Failed: " . $balance_stmt->error);
    }
    $balance_stmt->close();

    // Insert transaction history
    $transaction_query = "INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', 'Top-up approved via receipt')";
    $transaction_stmt = $conn->prepare($transaction_query);
    $transaction_stmt->bind_param('id', $user_id, $amount);
    if (!$transaction_stmt->execute()) {
        throw new Exception("Transaction History Insert Failed: " . $transaction_stmt->error);
    }
    $transaction_stmt->close();

    // Get updated balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_balance = $result->fetch_assoc()['balance'];
    $stmt->close();

    // Commit the transaction
    $conn->commit();

    // Return success response
    echo json_encode(['status' => 'success', 'updated_balance' => number_format($updated_balance, 2)]);

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
