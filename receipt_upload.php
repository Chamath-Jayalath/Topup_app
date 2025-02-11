<?php
// upload_receipt.php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['receipt']) && isset($_POST['amount'])) {
    $user_id = $_SESSION['user_id'];
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

    if ($amount === false || $amount <= 0) {
        $error_message = "Invalid amount. Please enter a positive number.";
    } else {
        $file = $_FILES['receipt'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($file['name']);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } else if ($file['size'] > 5000000) {
                $error_message = "File size must be less than 5MB.";
            } else {
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO receipts (user_id, file_name, amount, status) VALUES (?, ?, ?, 'Pending')");
                    $stmt->bind_param("isd", $user_id, $file_name, $amount);

                    if ($stmt->execute()) {
                        $success_message = "Receipt uploaded successfully! Awaiting verification.";
                    } else {
                        $error_message = "Error saving receipt: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Error moving uploaded file.";
                }
            }
        } else {
            $error_message = "File upload error code: " . $file['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="number"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: 0.3s;
        }
        input[type="number"]:focus, input[type="file"]:focus {
            border-color: #74ebd5;
            box-shadow: 0 0 5px rgba(116, 235, 213, 0.5);
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        button:hover {
            background: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #007BFF;
            transition: color 0.3s;
        }
        a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Receipt</h1>
        <?php if ($error_message): ?> <div class="error"><?php echo $error_message; ?></div> <?php endif; ?>
        <?php if ($success_message): ?> <div class="success"><?php echo $success_message; ?></div> <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required placeholder="Enter Amount">

            <label for="receipt">Select Receipt:</label>
            <input type="file" name="receipt" id="receipt" required>

            <button type="submit">Upload Receipt</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
