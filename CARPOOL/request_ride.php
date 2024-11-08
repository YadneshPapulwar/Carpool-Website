<?php
session_start();

// Database connection settings
$servername = "localhost"; // Update with your database server
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "carpool"; // Update with your database name

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the ride_id from the request
    $ride_id = isset($_POST['ride_id']) ? intval($_POST['ride_id']) : 0;
    $meetaddr = isset($_POST['meetaddr']) ? $_POST['meetaddr'] : '';
    $end = isset($_POST['end_location']) ? $_POST['end_location'] : '';
    $start = isset($_POST['start_location']) ? $_POST['start_location'] : '';

    // Get the passenger_id (user_id) from the session (assuming the user is logged in)
    if (isset($_SESSION['user_id'])) {
        $passenger_id = $_SESSION['user_id'];
        $username=$_SESSION['username'];

        // Check if the user is the driver of this ride
        $driver_check_sql = "SELECT user_id FROM rides WHERE id = ?";
        $driver_check_stmt = $conn->prepare($driver_check_sql);
        $driver_check_stmt->bind_param("i", $ride_id);
        $driver_check_stmt->execute();
        $driver_check_result = $driver_check_stmt->get_result();
        
        if ($driver_check_result->num_rows > 0) {
            $row = $driver_check_result->fetch_assoc();
            if ($row['user_id'] == $passenger_id) {
                // User is the driver, so they cannot request their own ride
                echo "You cannot request a ride for your own trip.";
            } else {
                // Prepare the SQL statement to insert the ride request
                $stmt = $conn->prepare("INSERT INTO ride_requests (ride_id, passenger_id, status, meeting_point, start, end, username) VALUES (?, ?, 'pending',?,?,?,?)");
                $stmt->bind_param("iissss", $ride_id, $passenger_id, $meetaddr, $start, $end, $username);

                // Execute the statement and check for success
                if ($stmt->execute()) {
                    echo "Ride request submitted successfully!";
                } else {
                    echo "Error submitting ride request: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            }
        } else {
            echo "Error: Ride not found.";
        }

        // Close the driver check statement
        $driver_check_stmt->close();
    } else {
        echo "Error: User not logged in.";
    }
} else {
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>
