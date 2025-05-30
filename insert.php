<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studentDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

//sql to insert data
$sql = "INSERT INTO Profile (firstname, lastname, email)
       VALUES ('Michael', 'Palmer', 'mpalmer@usep.edu.ph')";

if ($conn->query($sql) === TRUE)
    echo "New record created successfully";
else
    echo "Error: " . $sql . "<br>" . $conn->error;

//Close the database connection
$conn->close();
?>
