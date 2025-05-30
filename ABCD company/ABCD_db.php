<?php

// db connection parameters
$servername = "localhost";
$username = "root";      
$password = "";  

// connection to mysql server
$conn = new mysqli($servername, $username, $password);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// drop if db exists
$drop_query = "DROP DATABASE IF EXISTS abcd";
$conn->query($drop_query);

// create database
$sql = "CREATE DATABASE IF NOT EXISTS abcd";  
$conn->query($sql);

// connect to the newly created database
$conn->select_db("abcd");

// user table
$sql_user = "CREATE TABLE IF NOT EXISTS user (
    user_id INT(6) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    type ENUM('admin', 'customer') NOT NULL
)";
$conn->query($sql_user);

// user records 
$sql1u = "INSERT INTO user (name, username, password, type) 
        VALUES ('Juan dela Cruz', 'admin', 'admin', 'admin')";
$conn->query($sql1u);
$sql2u = "INSERT INTO user (name, username, password, type) 
        VALUES ('Michael Cors', 'cors','cors', 'customer')";
$conn->query($sql2u);

// package table
$sql_package = "CREATE TABLE IF NOT EXISTS package (
    package_id INT(6) AUTO_INCREMENT PRIMARY KEY,
    title_of_package VARCHAR(100) NOT NULL,
    description VARCHAR(50) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rate INT(9) NOT NULL
)";
$conn->query($sql_package);

// package records
$sql1p = "INSERT INTO package (title_of_package, description, rate) 
        VALUES ('sg25', 'singapore tour', '29000')";
$conn->query($sql1p);
$sql2p = "INSERT INTO package (title_of_package, description, rate) 
        VALUES ('my25', 'malaysia tour', '19000')";
$conn->query($sql2p);
$sql3p = "INSERT INTO package (title_of_package, description, rate)
        VALUES ('vn26', 'vietnam tour', '21000')";
$conn->query($sql3p);
$sql4p = "INSERT INTO package (title_of_package, description, rate)
        VALUES ('sokor26', 'sout korea tour', '50000')";
$conn->query($sql4p);
$sql5p = "INSERT INTO package (title_of_package, description, rate)
        VALUES ('use25', 'united states of america tour', '40000')";
$conn->query($sql5p);

// booking table
$sql_booking = "CREATE TABLE IF NOT EXISTS booking (
    booking_id INT(6) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(50) NOT NULL,
    number_of_persons INT(9) NOT NULL,
    booking_date DATE NOT NULL,
    avail_date DATE NOT NULL,
    customer_name VARCHAR(100),
    package_id INT(9),
    FOREIGN KEY (package_id) REFERENCES package(package_id)
)";
$conn->query($sql_booking);

// get customer name from user table and create a booking
$sql_get_customer = "SELECT name FROM user WHERE type = 'customer' LIMIT 1";
$result = $conn->query($sql_get_customer);

// booking record
$sql1b = "INSERT INTO booking (title, description, number_of_persons, booking_date, avail_date, customer_name, package_id)
        VALUES ('my25', 'malaysia tour', 15, '2025-06-29', '2025-05-15', '$customer_name', 2)";
$conn->query($sql1b);
    
// close connection
$conn->close();
?>