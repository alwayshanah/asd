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
// SQL to select records from Profile table
$sql = "SELECT id, firstname, lastname FROM Profile";
$result = $conn->query($sql);


// check if the number of rows in the result set is greater than 0 which means that record(s) exist
if ($result->num_rows > 0) 
{
    // output data of each row
    while($row = $result->fetch_assoc()) 
{
    echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . 
    $row["lastname"]. "<br>";
 }
} 
else 
{
    echo "0 results";
}
$conn->close();
?>