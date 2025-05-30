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
    // Redirect to home.php if no session
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Credentials</title>
</head>
<body>
    <h1>Login Credentials</h1>
    
    <div class="info-card">
        <div class="info-row">
            <span class="label">Username:</span> 
            <?php echo htmlspecialchars($_SESSION["username"]); ?>
        </div>
        <div class="info-row">
            <span class="label">Password:</span> 
            <?php echo htmlspecialchars($_SESSION["password"]); ?>
        </div>
    </div>
    
    <div class="nav-links">
        <a href="personal.php">View Personal Information</a>
    </div>
    
    <form method="POST">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</body>
</html>