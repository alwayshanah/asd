<?php
session_start();

// redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ABCD_login.php");
    exit();
}

// get user information from session
$user_id = $_SESSION['user_id'];
$customer_name = $_SESSION['name']; // Using customer name for bookings
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

// variables
$booking_message = '';
$booking_status = '';
$selected_packages = [];
$all_bookings = [];

// handle form submission for new bookings
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_packages'])) {
    try {
        $pdo->beginTransaction();
        
        // get selected package IDs
        $selected_ids = $_POST['selected_packages'];
        
        // insert each selected package into the booking table using customer_name
        foreach ($selected_ids as $package_id) {
            $insert_query = "INSERT INTO booking (customer_name, package_id, booking_date) 
                             VALUES (:customer_name, :package_id, NOW())";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':customer_name', $customer_name);
            $insert_stmt->bindParam(':package_id', $package_id);
            $insert_stmt->execute();
        }
        
        $pdo->commit();
        $booking_message = "Packages booked successfully!";
        $booking_status = "success";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $booking_message = "Error booking packages: " . $e->getMessage();
        $booking_status = "error";
    }
}

// handle booking cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    
    // verify booking if belongs to the user
    $check_query = "SELECT * FROM booking WHERE booking_id = :booking_id AND customer_name = :customer_name";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':booking_id', $booking_id);
    $check_stmt->bindParam(':customer_name', $customer_name);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        try {
            $delete_query = "DELETE FROM booking WHERE booking_id = :booking_id";
            $delete_stmt = $pdo->prepare($delete_query);
            $delete_stmt->bindParam(':booking_id', $booking_id);
            $delete_stmt->execute();
            
            $booking_message = "Booking cancelled successfully!";
            $booking_status = "success";
        } catch (PDOException $e) {
            $booking_message = "Error cancelling booking: " . $e->getMessage();
            $booking_status = "error";
        }
    } else {
        $booking_message = "You don't have permission to cancel this booking.";
        $booking_status = "error";
    }
}

// fetch all available packages
$query = "SELECT * FROM package ORDER BY package_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch all bookings for this customer with package details
$booking_query = "SELECT b.booking_id, b.customer_name, b.booking_date, 
                     p.package_id, p.title_of_package, p.description, p.rate, p.date_added 
                  FROM booking b
                  JOIN package p ON b.package_id = p.package_id
                  WHERE b.customer_name = :customer_name
                  ORDER BY b.booking_date DESC";
$booking_stmt = $pdo->prepare($booking_query);
$booking_stmt->bindParam(':customer_name', $customer_name);
$booking_stmt->execute();
$all_bookings = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        .submit-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 3px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }
        
        .submit-btn:hover {
            background-color: darkblue;
        }
        
        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        .checkbox-container {
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        
        .checkbox-container input {
            margin-right: 10px;
        }
        
        .user-info {
            background-color: #eaf4ff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .user-info span {
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .bookings-table th, 
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .bookings-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .bookings-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            margin-right: 5px;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        
        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Event Booking System</h2>
        <div class="nav-links">
            <a class="username">
                <?php echo htmlspecialchars($user_name); ?>
            </a>
            <a href="ABCD_welcome.php" class="nav-link">Welcome Page</a>
            <a href="ABCD_welcome.php?logout=true" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        
        <?php if (!empty($booking_message)): ?>
            <div class="<?php echo ($booking_status == 'error') ? 'error-message' : 'success-message'; ?>">
                <?php echo $booking_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" onclick="openTab(event, 'current-bookings')">My Bookings</div>
            <div class="tab" onclick="openTab(event, 'available-packages')">Available Packages</div>
        </div>
        
<!-- My Bookings Tab -->
        <div id="current-bookings" class="tab-content active section">
            <h2>My Current Bookings</h2>
            
            <?php if (empty($all_bookings)): ?>
                <div class="no-packages">You don't have any bookings yet.</div>
            <?php else: ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Package Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Booking Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['title_of_package']); ?></td>
                                <td><?php echo htmlspecialchars($booking['description']); ?></td>
                                <td>$<?php echo htmlspecialchars($booking['rate']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                <td>
                                    <a href="ABCD_saveBooking.php?cancel=<?php echo $booking['booking_id']; ?>" 
                                       class="cancel-btn" 
                                       onclick="return confirm('Are you sure you want to cancel this booking?');">
                                        Cancel
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

<!-- Available Packages Tab-->        
        <div id="available-packages" class="tab-content section">
            <h2>Book New Packages</h2>
            
            <form method="post" action="ABCD_saveBooking.php">
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
                                        <span class="detail-label">Rate:</span>
                                        <span>$<?php echo htmlspecialchars($package['rate']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="checkbox-container">
                                    <input type="checkbox" name="selected_packages[]" value="<?php echo $package['package_id']; ?>">
                                    <label>Select this package</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
            <button type="submit" class="submit-btn">Book</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        function openTab(evt, tabName) {
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            var tabs = document.getElementsByClassName("tab");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>