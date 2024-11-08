<?php
// Database connection details
$servername = "localhost";  // Adjust if your database server is different
$username = "root";         // Your database username
$password = "";             // Your database password
$dbname = "carpool";        // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current date
$currentDate = date("Y-m-d");

// Query to fetch rides from the database, excluding past rides based on the date only
$sql = "SELECT id, start_lat, start_lng, end_lat, end_lng, start_datetime, seats_available, custom_price, driver_name, phone
        FROM rides 
        WHERE seats_available > 0 AND DATE(start_datetime) >= ?"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to hold ride data
$rides = [];

if ($result->num_rows > 0) {
    // Fetch each ride and add to the array
    while($row = $result->fetch_assoc()) {
        $rides[] = $row;
    }
}

// Return the ride data in JSON format
header('Content-Type: application/json');
echo json_encode($rides);

// Close the database connection
$stmt->close();
$conn->close();
?>
