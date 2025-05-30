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

// delete 
$delete_sql = "DELETE FROM profile WHERE id = 2";
if ($conn->query($delete_sql) === TRUE) {
    echo "Record deleted successfully<br>";
} else {
    echo "Error deleting record: " . $conn->error . "<br>";
}

// update 
$update_sql = "UPDATE profile SET firstname = 'Jake' WHERE id = 1";
if ($conn->query($update_sql) === TRUE) {
    echo "Record updated successfully<br>";
} else {
    echo "Error updating record: " . $conn->error . "<br>";
}

//Close the database connection
$conn->close();
?>