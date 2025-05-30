<?php
session_start();

// redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ABCD_login.php");
    exit();
}

// redirect user if logged out
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ABCD_login.php");
    exit();
}

// get user information from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_type = $_SESSION['type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABCD Tour</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .header {
            background-color: #333;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .username {
            margin-right: 20px;
        }
        
        .logout-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: blue;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        .welcome-message {
            margin-bottom: 30px;
        }
        
        .user-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .action-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            flex: 1;
            min-width: 250px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .action-card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .action-card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .action-link {
            display: inline-block;
            background-color: blue;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .action-link:hover {
            background-color: darkblue;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 8px;
        }
        
        .badge-admin {
            background-color: #ff9800;
            color: white;
        }
        
        .badge-customer {
            background-color: #2196F3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Event Booking System</h2>
        <div class="user-info">
            <span class="username">
                <?php echo htmlspecialchars($user_name); ?>
            </span>
            <a href="?logout=true" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-message">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <?php if ($user_type == 'admin'): ?>
                <p>This is the admin welcome page.</p>
            <?php else: ?>
                <p>This is the customer welcome page.</p>
            <?php endif; ?>
        </div>
        
        <div class="user-actions">
            <?php if ($user_type == 'admin'): ?>
<!-- admin -->
                <div class="action-card">
                    <h3>New Package</h3>
                    <p>Create a new tour package to offer to customers.</p>
                    <a href="ABCD_newPackage.php" class="action-link">Create Package</a>
                </div>
                
                <div class="action-card">
                    <h3>View Packages</h3>
                    <p>View existing tour packages.</p>
                    <a href="ABCD_viewPackages.php" class="action-link">Manage Packages</a>
                </div>
            <?php else: ?>
<!-- customer -->
                <div class="action-card">
                    <h3>View Packages</h3>
                    <p>Browse available tour packages and book your next trip.</p>
                    <a href="ABCD_viewPackages.php" class="action-link">View Packages</a>
                </div>
                
                <div class="action-card">
                    <h3>Saved Bookings</h3>
                    <p>View and manage your saved bookings.</p>
                    <a href="ABCD_saveBooking.php" class="action-link">My Bookings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>