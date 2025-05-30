<?php
session_start();

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $course = $_POST["course"];
    $school = $_POST["school"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // set session variables
    $_SESSION["firstName"] = $firstName;
    $_SESSION["lastName"] = $lastName;
    $_SESSION["course"] = $course;
    $_SESSION["school"] = $school;
    $_SESSION["username"] = $username;
    $_SESSION["password"] = $password;
    $_SESSION["loggedIn"] = true;
    
    // redirect to personal.php
    header("Location: personal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
</head>
<body>
    <h1>Registration Form</h1>
    <p>Please fill out the form below to register and begin a session.</p>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required>
        </div>
        
        <div class="form-group">
            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required>
        </div>
        
        <div class="form-group">
            <label for="course">Course:</label>
            <input type="text" id="course" name="course" required>
        </div>
        
        <div class="form-group">
            <label for="school">School:</label>
            <input type="text" id="school" name="school" required>
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Register</button>
    </form>
</body>
</html>