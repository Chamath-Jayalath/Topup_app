<?php
// Configure session security
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);

session_start();
require 'db.php'; 

define("ERROR_EMPTY_FIELDS", "Please fill in all fields.");
define("ERROR_INVALID_CREDENTIALS", "Invalid admin credentials.");
define("ERROR_DATABASE_ERROR", "An error occurred. Please try again later.");

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = ERROR_EMPTY_FIELDS;
    } else {
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        try {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['role'] = $admin['role'];

                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $error_message = ERROR_INVALID_CREDENTIALS;
                }
            } else {
                $error_message = ERROR_INVALID_CREDENTIALS;
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error_message = ERROR_DATABASE_ERROR;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-4 p-4 bg-light rounded shadow">
            <h2 class="text-center">Admin Login</h2>
            <?php if ($error_message) echo "<div class='alert alert-danger'>$error_message</div>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="mt-3 text-center"><a href="login.php">User Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>
