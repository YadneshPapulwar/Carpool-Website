<?php
session_start();
include("connection.php");

$error_message = ""; // Initialize an error message variable

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM user WHERE username=? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            if (password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['user_id'];
                $_SESSION['username']=$user_data['username'];
                header("Location: dashboard.php"); // Redirect to dashboard
                exit;
            } else {
                $error_message = "Invalid credentials"; // Set error message
            }
        } else {
            $error_message = "User not found"; // Set error message
        }

        $stmt->close();
    } else {
        $error_message = "Please enter some valid information"; // Set error message
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="nav"></div>
    <div class="banner"></div>
    <div class="container1">
        <div class="imgcontainer">
            <img class="bg-image" src="./images/BG-login.jpg" alt="">
        </div>
        <div class="container">
            <div class="logo">
                <img src="../images/somaiya-vidyavihar-brand.svg" alt="">
            </div>
            <h2>Login</h2>
            <form action="#" method="post">
                <div class="input">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </form>
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    <?php echo htmlspecialchars($error_message); // Display the error message ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
