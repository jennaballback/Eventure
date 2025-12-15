<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'includes/db.php';

$message = '';

// Only process the form if it was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data safely
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name'])  ? trim($_POST['last_name'])  : '';
    $email      = isset($_POST['email'])      ? trim($_POST['email'])      : '';
    $password   = isset($_POST['password'])   ? trim($_POST['password'])   : '';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute insert statement
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Registration successful! <a href='login.php'>Login here</a>.";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Eventure</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card mx-auto login-card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Register</h3>

                <?php if (!empty($message)) : ?>
                    <div class="alert alert-danger text-center"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name:</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Register</button>
                </form>

                <div class="register-link mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
