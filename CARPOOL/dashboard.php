<?php
session_start();
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's current rides
$query = "SELECT * FROM rides WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rides = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's ride requests
$requestQuery = "SELECT * FROM ride_requests WHERE ride_id IN (Select id from rides where user_id = ?)";
$requestStmt = $conn->prepare($requestQuery);
$requestStmt->bind_param("i", $user_id);
$requestStmt->execute();
$requestResult = $requestStmt->get_result();
$rideRequests = $requestResult->fetch_all(MYSQLI_ASSOC);

$requestQuery1 = "
    SELECT r.*, rr.status
    FROM rides r
    JOIN ride_requests rr ON r.id = rr.ride_id
    WHERE rr.passenger_id = ?
";
$requestStmt1 = $conn->prepare($requestQuery1);
$requestStmt1->bind_param("i", $user_id);
$requestStmt1->execute();
$requestResult1 = $requestStmt1->get_result();
$rideRequests1 = $requestResult1->fetch_all(MYSQLI_ASSOC);

// Close the prepared statements and the database connection
$stmt->close();
$requestStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa; /* Light background for contrast */
        color: #333; /* Dark text color for readability */
    }

    .navbar {
        background-color: #343a40; /* Darker navbar */
    }

    .nav-link {
        font-size: 1.2rem;
        color: #ffffff; /* White text color */
    }

    .navbar1 {
        text-align: center;
        margin: 20px 0;
    }

    .container {
        max-width: 1200px; /* Limit the maximum width */
        margin: auto; /* Center the container */
        padding: 20px; /* Add padding */
        background-color: #ffffff; /* White background for content area */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
    }

    h1, h2 {
        color: #343a40; /* Darker headings */
        text-align: left; /* Align headings to the left */
    }

    .table {
        width: 100%; /* Full width for the table */
        margin-bottom: 1.5rem; /* Space below tables */
        background-color: #ffffff; /* White background for tables */
        border-radius: 0.5rem; /* Rounded corners for tables */
        overflow: hidden; /* Prevent overflow of rounded corners */
    }

    .table thead {
        background-color: #007bff; /* Blue header background */
        color: #ffffff; /* White header text */
    }

    .table th, .table td {
        padding: 12px; /* Increased padding for table cells */
        vertical-align: middle; /* Center align content vertically */
        text-align: left; /* Align text to the left */
    }

    .table th {
        font-weight: bold; /* Bold text for table headers */
    }

    .table tr {
        transition: background-color 0.3s; /* Smooth transition for background */
    }

    .table tr:hover {
        background-color: #f1f1f1; /* Light gray background on row hover */
    }

    .btn {
        border-radius: 5px; /* Rounded button corners */
        transition: background-color 0.3s; /* Smooth transition for background */
    }

    .btn-success {
        background-color: #28a745; /* Green for accept button */
    }

    .btn-danger {
        background-color: #dc3545; /* Red for decline button */
    }

    .btn-success:hover {
        background-color: #218838; /* Darker green on hover */
    }

    .btn-danger:hover {
        background-color: #c82333; /* Darker red on hover */
    }

    /* Responsive styling for smaller screens */
    @media (max-width: 768px) {
        .nav-link {
            font-size: 1rem; /* Smaller font size for mobile */
        }
        
        h1, h2 {
            font-size: 1.5rem; /* Adjusted heading sizes for mobile */
        }

        .table th, .table td {
            padding: 8px; /* Reduced padding for smaller screens */
        }
    }

    .nav-link {
        font-size: 1.2rem; /* Adjust font size of navbar items */
    }
</style>
<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
        <img src="images/somaiya-vidyavihar-brand.svg" height="40" width="60">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample03" aria-controls="navbarsExample03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="navbarsExample03">
            <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.html">Driver</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="passenger.html">Passenger</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Dashboard</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="logout.php">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="navbar1">
    <h1>Welcome to Your Dashboard</h1>
</div>

<div class="container">
<h2>Your Current Rides</h2>
<?php if (empty($rides)): ?>
    <p>You have no active rides at the moment.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Start Location</th>
                <th>End Location</th>
                <th>Start Time</th>
                <th>Actions</th> <!-- Added Actions column -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rides as $ride): ?>
                <tr>
                    <td data-lat="<?php echo htmlspecialchars($ride['start_lat']); ?>" data-lng="<?php echo htmlspecialchars($ride['start_lng']); ?>">Fetching address...</td>
                    <td data-lat="<?php echo htmlspecialchars($ride['end_lat']); ?>" data-lng="<?php echo htmlspecialchars($ride['end_lng']); ?>">Fetching address...</td>
                    <td><?php echo htmlspecialchars(date("d-m-Y H:i", strtotime($ride['start_datetime']))); ?></td>
                    <td>
                        <form action="delete_ride.php" method="post" style="display:inline;" onsubmit="return confirmDelete();">
                            <input type="hidden" name="ride_id" value="<?php echo htmlspecialchars($ride['id']); ?>">
                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Requests for your Ride</h2>
<?php if (empty($rideRequests)): ?>
    <p>You have no pending ride requests.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Username</th> 
                <th>Start Location</th> <!-- New Column -->
                <th>End Location</th>   <!-- New Column -->
                <th>Meeting Point</th>  <!-- New Column -->
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rideRequests as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['username']); ?></td>    
                    <td><?php echo htmlspecialchars($request['start']); ?></td>
                    <td><?php echo htmlspecialchars($request['end']); ?></td>
                    <td><?php echo htmlspecialchars($request['meeting_point']); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <?php if ($request['status'] === 'pending'): ?>
                            <form action="handle_request.php" method="post" style="display:inline;" onsubmit="return confirmAccept();">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                                <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                            </form>
                            <form action="handle_request.php" method="post" style="display:inline;" onsubmit="return confirmDecline();">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                                <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Ride Requests You've Made</h2>
<?php if (empty($rideRequests1)): ?>
    <p>You have no ride requests.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Start Location</th>
                <th>End Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rideRequests1 as $request): ?>
                <tr>
                    <td data-lat="<?php echo htmlspecialchars($request['start_lat']); ?>" data-lng="<?php echo htmlspecialchars($request['start_lng']); ?>">Fetching address...</td>
                    <td data-lat="<?php echo htmlspecialchars($request['end_lat']); ?>" data-lng="<?php echo htmlspecialchars($request['end_lng']); ?>">Fetching address...</td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <form action="cancel_request.php" method="post" style="display:inline;" onsubmit="return confirmDelete();">
                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                            <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<script>
    const apiKey = 'LHb0PtgzKlGLpp4YTM6oHgQTcUnF4DuEb76reivs'; // Replace with your OlaMaps API key

    function reverseGeocode(lat, lng) {
        const geocodeUrl = `https://api.olamaps.io/places/v1/reverse-geocode?latlng=${lat},${lng}&api_key=${apiKey}`;

        return fetch(geocodeUrl, {
            method: 'GET',
            headers: {
                'accept': 'application/json',
                'X-Request-Id': 'esha',
                'X-Correlation-Id': 'carpool',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Reverse geocoding error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.results && data.results.length > 0) {
                return data.results[0].formatted_address;
            } else {
                return 'Address not found';
            }
        })
        .catch(error => {
            console.error('Error in reverse geocoding:', error);
            return 'Error fetching address';
        });
    }

    function updateRideAddresses() {
        const rideRows = document.querySelectorAll('tbody tr');

        rideRows.forEach(async (row) => {
            const startLocationCell = row.cells[0];
            const endLocationCell = row.cells[1];

            const startLat = startLocationCell.getAttribute('data-lat');
            const startLng = startLocationCell.getAttribute('data-lng');
            const endLat = endLocationCell.getAttribute('data-lat');
            const endLng = endLocationCell.getAttribute('data-lng');

            if (startLat && startLng) {
                const startAddress = await reverseGeocode(startLat, startLng);
                startLocationCell.innerText = startAddress;
            }

            if (endLat && endLng) {
                const endAddress = await reverseGeocode(endLat, endLng);
                endLocationCell.innerText = endAddress;
            }
        });
    }

    function confirmDelete() {
        return confirm("Are you sure you want to delete this ride? This action cannot be undone.");
    }

    function confirmAccept() {
        return confirm("Are you sure you want to accept this ride request?");
    }

    function confirmDecline() {
        return confirm("Are you sure you want to decline this ride request?");
    }

    window.onload = updateRideAddresses;
</script>

</body>
</html>
