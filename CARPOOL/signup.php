<?php
include("connection.php");
include("functions.php");

$error = ""; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Validate email domain
    if (strpos($email, '@somaiya.edu') !== false) {
        if (!empty($username) && !empty($password) && !empty($email)) {
            // Check if the username or email already exists
            $checkQuery = "SELECT * FROM user WHERE username=? OR email=?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ss", $username, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                // If there is a duplicate entry
                $error = "Username or email already exists.";
            } else {
                // Proceed with insertion
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $username, $email, $passwordHash);
                    if ($stmt->execute()) {
                        header("Location: login.php");
                        die;
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
            $checkStmt->close();
        } else {
            $error = "Please enter some valid information";
        }
    } else {
        $error = "Please use a valid somaiya email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-Up Page</title>
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
            <h2>Sign Up</h2>
            <form action="#" method="post">
                <div class="input">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Sign Up</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
                <?php if (!empty($error)): ?>
                    <p class='error' style='color: red;'><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
