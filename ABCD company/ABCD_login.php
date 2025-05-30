<?php
session_start();

// redirect user if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ABCD_welcome.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "abcd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = "";

// only allow access to users whose username and password are already registered in the user table
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $username = $conn->real_escape_string($username);
    
    $sql = "SELECT user_id, name, username, password, type 
            FROM user 
            WHERE username = '$username' AND password = '$password'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['type'] = $user['type'];
        
        header("Location: ABCD_welcome.php");
        exit();
    } else {
        $login_error = "Account does not exist!";
    }
}

$conn->close();
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
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-container {
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 350px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        button {
            background-color: blue;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 3px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: darkblue;
        }
        
        .error {
            color: #ff0000;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>ABCD Tour</h1>
        
        <?php if (!empty($login_error)): ?>
            <div class="error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>