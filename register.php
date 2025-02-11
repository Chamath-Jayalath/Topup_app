<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim and sanitize input
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Use prepared statements to prevent SQL injection
    $query = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            echo '<div class="success-message">User registered successfully!</div>';
        } else {
            echo '<div class="error-message">Error: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    } else {
        echo '<div class="error-message">Error: ' . $conn->error . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" href="https://www.example.com/favicon.ico" type="image/x-icon">
    <style>
        /* General Styling */
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        h2 {
            color: #007BFF;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .input-group {
            display: flex;
            align-items: center;
            background: #f4f4f4;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .input-group img {
            width: 20px;
            margin-right: 10px;
        }
        input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 16px;
        }
        .register-button {
            width: 100%;
            padding: 12px;
            background: #6b73ff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .register-button:hover {
            background: #5a62d6;
        }
        .login-link {
            color: #007BFF;
            text-decoration: none;
        }
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>
            <img src="https://img.icons8.com/ios/50/user-male-circle.png" width="30" alt="User Icon">
            Register
        </h2>
        <form method="POST" action="register.php">
            <div class="input-group">
                <img src="https://img.icons8.com/ios/452/user-male-circle.png" alt="User Icon">
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <img src="https://img.icons8.com/ios/452/lock.png" alt="Lock Icon">
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="register-button">Register</button>
        </form>
        <p>Already have an account? <a href="login.php" class="login-link">Login here</a>.</p>
    </div>
</body>
</html>
