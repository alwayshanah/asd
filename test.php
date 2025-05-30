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

// redirect if not admin
if ($user_type != 'admin') {
    header("Location: ABCD_welcome.php");
    exit();
}

// db connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "abcd";

// connection to mysql server
$conn = new mysqli($servername, $username, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// variables
$title = $description = $rate = "";
$titleErr = $descriptionErr = $rateErr = "";
$success_message = "";
$error_message = "";

// process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["title"])) {
        $titleErr = "Title is required";
    } else {
        $title = trim($_POST["title"]);
    }
    
    if (empty($_POST["description"])) {
        $descriptionErr = "Description is required";
    } else {
        $description = trim($_POST["description"]);
    }
    
    if (empty($_POST["rate"])) {
        $rateErr = "Rate is required";
    } else {
        $rate = trim($_POST["rate"]);
        if (!is_numeric($rate) || $rate <= 0) {
            $rateErr = "Rate must be a positive number";
        }
    }
    
    if (empty($titleErr) && empty($descriptionErr) && empty($rateErr)) {

        $date_added = date("Y-m-d");
        
        $sql = "INSERT INTO package (title_of_package, description, date_added, rate) VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssd", $title, $description, $date_added, $rate);
        
        if ($stmt->execute()) {
            $success_message = "New package added successfully!";
            $title = $description = $rate = "";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Booking System - New Package</title>
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
            background-color: darkblue;
        }
        
        .navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .nav-link {
            background-color: blue;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
        }
        
        .nav-link:hover {
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        textarea {
            height: 150px;
            resize: vertical;
        }
        
        .error {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .submit-btn {
            background-color: blue;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        
        .submit-btn:hover {
            background-color: darkblue;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            <a href="ABCD_welcome.php?logout=true" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="navigation">
            <a href="ABCD_welcome.php" class="nav-link">Welcome Page</a>
            <a href="ABCD_view_packages.php" class="nav-link">View Packages</a>
        </div>
        
        <h1>Create New Package</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="title">Package Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>">
                <?php if (!empty($titleErr)): ?>
                    <span class="error"><?php echo $titleErr; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
                <?php if (!empty($descriptionErr)): ?>
                    <span class="error"><?php echo $descriptionErr; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="rate">Rate (₱):</label>
                <input type="number" id="rate" name="rate" step="0.01" min="0" value="<?php echo htmlspecialchars($rate); ?>">
                <?php if (!empty($rateErr)): ?>
                    <span class="error"><?php echo $rateErr; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="submit-btn">Add Package</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
// close connection
$conn->close();
?>