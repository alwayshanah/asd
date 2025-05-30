<?php
session_start();

// handle logout
if (isset($_POST['logout'])) {
    // unset all session variables
    $_SESSION = array();
    
    // destroy the session
    session_destroy();
    
    // redirect to home.php
    header("Location: home.php");
    exit();
}

// check if user is logged in
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    // redirect to home.php if no session
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information</title>
</head>
<body>
    <h1>Personal Information</h1>
    
    <div class="info-card">
        <div class="info-row">
            <span class="label">First Name:</span> 
            <?php echo htmlspecialchars($_SESSION["firstName"]); ?>
        </div>
        <div class="info-row">
            <span class="label">Last Name:</span> 
            <?php echo htmlspecialchars($_SESSION["lastName"]); ?>
        </div>
        <div class="info-row">
            <span class="label">Course:</span> 
            <?php echo htmlspecialchars($_SESSION["course"]); ?>
        </div>
        <div class="info-row">
            <span class="label">School:</span> 
            <?php echo htmlspecialchars($_SESSION["school"]); ?>
        </div>
    </div>
    
    <div class="nav-links">
        <a href="login.php">View Login Credentials</a>
    </div>
    
    <form method="POST">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</body>
</html>