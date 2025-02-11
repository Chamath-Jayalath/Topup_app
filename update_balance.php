<?php
require 'db.php';
session_start();

// Check if user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get the receipt ID and the new balance from the request
if (isset($_GET['id']) && isset($_GET['balance'])) {
    $receipt_id = $_GET['id'];
    $new_balance = $_GET['balance'];

    // Update the user's balance in the database
    $query = "UPDATE users u JOIN receipts r ON u.id = r.user_id SET u.balance = ? WHERE r.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('di', $new_balance, $receipt_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update balance.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
