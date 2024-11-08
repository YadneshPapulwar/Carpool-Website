<?php
// handle_request.php

require 'connection.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'accept' or 'decline'

    if ($action === 'accept') {
        $status = 'accepted';
    } elseif ($action === 'decline') {
        $status = 'declined';
    } else {
        // Handle invalid action
        die("Invalid action");
    }

    // Update the ride request status
    $query = "UPDATE ride_requests SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        echo "Ride request {$status} successfully.";

        // If request is accepted, reduce seats_available by 1 in the rides table
        if ($status === 'accepted') {
            // Get the ride_id for this request
            $rideQuery = "SELECT ride_id FROM ride_requests WHERE request_id = ?";
            $rideStmt = $conn->prepare($rideQuery);
            $rideStmt->bind_param("i", $request_id);
            $rideStmt->execute();
            $rideStmt->bind_result($ride_id);
            $rideStmt->fetch();
            $rideStmt->close();

            // Decrease seats_available by 1 for the specific ride
            $seatsQuery = "UPDATE rides SET seats_available = seats_available - 1 WHERE id = ? AND seats_available > 0";
            $seatsStmt = $conn->prepare($seatsQuery);
            $seatsStmt->bind_param("i", $ride_id);

            if ($seatsStmt->execute()) {
                echo "Seats available reduced by one.";
            } else {
                echo "Error updating seats available: " . $seatsStmt->error;
            }

            $seatsStmt->close();
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
