<?php
session_start();

// redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ABCD_login.php");
    exit();
}

// get user information from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_type = $_SESSION['type'];

// db connection
$host = "localhost";  
$dbname = "abcd";  
$username = "root";  
$password = "";  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// fetch all packages
$query = "SELECT * FROM package ORDER BY package_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// handle delete action for admin
if ($user_type == 'admin' && isset($_GET['delete'])) {
    $package_id = $_GET['delete'];

    try {
        // delete first any related bookings 
        $delete_bookings_query = "DELETE FROM booking WHERE package_id = :package_id";
        $delete_bookings_stmt = $pdo->prepare($delete_bookings_query);
        $delete_bookings_stmt->bindParam(':package_id', $package_id);
        $delete_bookings_stmt->execute();

        // then delete the package
        $delete_package_query = "DELETE FROM package WHERE package_id = :package_id";
        $delete_stmt = $pdo->prepare($delete_package_query);
        $delete_stmt->bindParam(':package_id', $package_id);
        $delete_stmt->execute();

        // redirect to prevent resubmission on refresh
        header("Location: ABCD_viewPackages.php?deleted=true");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error deleting package: " . $e->getMessage();
    }
}
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
        
        .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .nav-link, .logout-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .nav-link:hover, .logout-btn:hover {
            background-color: darkblue;
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
        
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .package-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .package-title {
            font-size: 20px;
            margin-top: 0;
            color: #333;
            margin-bottom: 10px;
        }
        
        .package-details {
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .package-description {
            margin-bottom: 20px;
            color: #666;
        }
        
        .package-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .action-button {
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
        }
        
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        
        .book-btn {
            background-color: blue;
            color: white;
            flex-grow: 1;
        }
        
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .no-packages {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 18px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 50%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        
        .form-buttons {
            display: flex;
            gap: 10px;
        }
        
        .submit-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .cancel-btn {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .submit-btn:hover {
            background-color: darkblue;
        }
        
        .cancel-btn:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>ABCD Tour</h2>
        <div class="nav-links">
            <a href="ABCD_welcome.php" class="nav-link">Welcome Page</a>
            <?php if ($user_type == 'admin'): ?>
                <a href="ABCD_newPackage.php" class="nav-link">New Package</a>
            <?php endif; ?>
            <a href="ABCD_welcome.php?logout=true" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <h1>Tour Packages</h1>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
            <div class="success-message">Package deleted successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($booking_message)): ?>
            <div class="<?php echo ($booking_status == 'error') ? 'error-message' : 'success-message'; ?>">
                <?php echo $booking_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($packages)): ?>
            <div class="no-packages">No packages available at the moment.</div>
        <?php else: ?>
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <h3 class="package-title"><?php echo htmlspecialchars($package['title_of_package']); ?></h3>
                        
                        <div class="package-details">
                            <div class="detail-row">
                                <span class="detail-label">Description</span>
                                <span><?php echo htmlspecialchars($package['description']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Date Added:</span>
                                <span><?php echo htmlspecialchars($package['date_added']); ?> days</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Rate:</span>
                                <span>$<?php echo htmlspecialchars($package['rate']); ?></span>
                            </div>
                        </div>
                        
                        <div class="package-actions">
                            <?php if ($user_type == 'admin'): ?>
                                <a href="ABCD_viewPackages.php?delete=<?php echo $package['package_id']; ?>" class="action-button delete-btn" onclick="return confirm('Are you sure you want to delete this package?');">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>