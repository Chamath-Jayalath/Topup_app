<?php
// Configure session cookie security (MUST be before session_start())
ini_set('session.cookie_secure', 1); // Only send cookies over HTTPS
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to cookies

session_start();

require 'db.php'; // Make sure this path is correct

// Define error message constants
define("ERROR_EMPTY_FIELDS", "Please fill in all fields.");
define("ERROR_INVALID_CREDENTIALS", "Invalid credentials.");
define("ERROR_USERNAME_LENGTH", "Username must be between 3 and 50 characters.");
define("ERROR_USERNAME_FORMAT", "Username can only contain letters, numbers, and underscores.");
define("ERROR_DATABASE_ERROR", "An error occurred. Please try again later.");

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = ERROR_EMPTY_FIELDS;
    } else {
        // Sanitize and validate input
        $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        if (strlen($username) < 3 || strlen($username) > 50) {
            $error_message = ERROR_USERNAME_LENGTH;
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error_message = ERROR_USERNAME_FORMAT;
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
                if ($stmt === false) {
                    error_log("Prepare failed: " . $conn->error);
                    $error_message = ERROR_DATABASE_ERROR;
                } else {
                    $stmt->bind_param("s", $username);

                    if ($stmt->execute() === false) {
                        error_log("Execute failed: " . $stmt->error);
                        $error_message = ERROR_DATABASE_ERROR;
                    } else {
                        $result = $stmt->get_result();
                        if ($result === false) {
                            error_log("get_result failed: " . $stmt->error);
                            $error_message = ERROR_DATABASE_ERROR;
                        } else {
                            if ($result->num_rows == 1) {
                                $user = $result->fetch_assoc();

                                if (password_verify($password, $user['password'])) {
                                    session_regenerate_id(true); // Regenerate session ID
                                    $_SESSION['user_id'] = $user['id'];
                                    $_SESSION['username'] = $user['username'];
                                    $_SESSION['role'] = $user['role'];

                                    if ($_SESSION['role'] === 'admin') {
                                        header("Location: admin_dashboard.php");
                                    } else {
                                        header("Location: dashboard.php");
                                    }
                                    exit();
                                } else {
                                    $error_message = ERROR_INVALID_CREDENTIALS;
                                }
                            } else {
                                $error_message = ERROR_INVALID_CREDENTIALS;
                            }
                        }
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $error_message = ERROR_DATABASE_ERROR;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .login-container {
            max-width: 400px;
            padding: 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }

        .login-container:hover {
            transform: scale(1.03);
        }

        .input-group-text {
            background: #764ba2;
            color: white;
            border: none;
        }

        .btn-primary {
            background: #764ba2;
            border: none;
            transition: background 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background: #5a3b8b;
        }

        .eye-toggle {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2 class="mb-4 text-center text-primary"><i class="fas fa-user"></i> Login</h2>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <span class="input-group-text eye-toggle" id="togglePassword"><i class="fas fa-eye" id="eyeIcon"></i></span>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a>.</p>
        </form>
    </div>

    <script>
        document.querySelector('#togglePassword').addEventListener('click', function() {
            const passwordField = document.querySelector('#password');
            const eyeIcon = document.querySelector('#eyeIcon');
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
