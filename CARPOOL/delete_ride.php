<?php
session_start();
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ride_id'])) {
    $ride_id = $_POST['ride_id'];

    // First, delete associated ride requests
    $deleteRequestsQuery = "DELETE FROM ride_requests WHERE ride_id = ?";
    $requestStmt = $conn->prepare($deleteRequestsQuery);
    $requestStmt->bind_param("i", $ride_id);
    $requestStmt->execute();
    $requestStmt->close();

    // Now delete the ride
    $deleteRideQuery = "DELETE FROM rides WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteRideQuery);
    $stmt->bind_param("ii", $ride_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Redirect back to dashboard with a success message
        header("Location: dashboard.php?message=Ride deleted successfully.");
        exit;
    } else {
        // Handle deletion error
        echo "Error deleting ride: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
