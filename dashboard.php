<?php
// Secure session handling
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch username and balance in one query
    $stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
    if ($stmt === false) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($username, $balance);
    $stmt->fetch();
    $stmt->close();

    // Default balance if null
    if ($balance === null) {
        $balance = 0.00;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("An error occurred fetching user data.");
}

$conn->close();

// Function to generate the calendar
function generateCalendar($month, $year) {
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = date('t', $firstDay);
    $dayOfWeek = date('w', $firstDay);
    
    echo "<table class='calendar-table'>";
    echo "<thead><tr>";
    foreach ($daysOfWeek as $day) {
        echo "<th>$day</th>";
    }
    echo "</tr></thead><tbody><tr>";
    
    // Empty cells before the first day of the month
    for ($i = 0; $i < $dayOfWeek; $i++) {
        echo "<td></td>";
    }
    
    // Days of the month
    for ($day = 1; $day <= $daysInMonth; $day++) {
        if (($day + $dayOfWeek - 1) % 7 == 0) {
            echo "</tr><tr>";
        }
        echo "<td>$day</td>";
    }
    
    echo "</tr></tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --bg-color: rgb(255, 255, 255);
            --text-color: #34495e;
            --balance-color: #27ae60;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: var(--bg-color);
            margin: 0;
            padding: 0;
            transition: background var(--transition-speed), color var(--transition-speed);
        }

        .dashboard-container {
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            position: relative;
            transition: transform var(--transition-speed);
        }

        .dashboard-container:hover {
            transform: scale(1.02);
        }

        h2 {
            color: var(--text-color);
            font-size: 28px;
            font-weight: bold;
        }

        p {
            color: var(--text-color);
            font-size: 18px;
        }

        .balance {
            font-size: 22px;
            color: var(--balance-color);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color var(--transition-speed);
        }

        .balance i {
            margin-right: 10px;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            color: #fff;
            background: var(--primary-color);
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background var(--transition-speed), transform var(--transition-speed);
        }

        .zoom-request button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: var(--primary-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .zoom-request button:hover {
            background-color: var(--secondary-color); /* Change to a darker shade when hovered */
            transform: scale(1.05); /* Slightly enlarge the button */
        }

        .calendar {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($username); ?></p>
        <div class="balance">
            <i class="fas fa-wallet"></i> Your Balance: LKR <span id="balance"><?php echo number_format($balance, 2); ?></span>
        </div>
        <div class="links">
            <a href="receipt_upload.php">Upload Bank Receipt</a>
            <a href="transaction_history.php">Transaction History</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Zoom Request Form -->
        <div class="zoom-request">
            <form action="zoom_request.php" method="POST">
                <button type="submit">Request Zoom Link</button>
            </form>
        </div>

        <div class="calendar">
            <h3>Calendar - <?php echo date('F Y'); ?></h3>
            <?php generateCalendar(date('m'), date('Y')); ?>
        </div>
    </div>
</body>
</html>
