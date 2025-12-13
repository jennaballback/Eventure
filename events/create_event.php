<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = !empty($_POST['description']) ? trim($_POST['description']) : null;
    $location    = !empty($_POST['location']) ? trim($_POST['location']) : null;
    $theme       = !empty($_POST['theme']) ? trim($_POST['theme']) : null;
    $start_time  = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time    = !empty($_POST['end_time']) ? trim($_POST['end_time']) : null;
    $host_id     = $_SESSION['user_id'];
    $is_canceled = 0;

    // IMAGE UPLOAD HANDLING
    $image_path = null;

    if (!empty($_FILES['event_image']['name'])) {

        $upload_dir = "../uploads/events/";

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = time() . "_" . basename($_FILES['event_image']['name']);
        $target_path = $upload_dir . $filename;

        $mime = mime_content_type($_FILES['event_image']['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mime, $allowed_types)) {
            $message = "Only JPG, PNG, GIF, or WEBP images are allowed.";
        } else {
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_path)) {
                // Path stored in DB (relative to project root)
                $image_path = "uploads/events/" . $filename;
            } else {
                $message = "Failed to upload image.";
            }
        }
    }

    // FORM VALIDATION
    if (empty($message)) {
        if (empty($title) || empty($start_time)) {
            $message = "Please fill in the title and start time.";
        } else {

            $stmt = $conn->prepare(
                "INSERT INTO events
                (host_id, title, description, location, start_time, end_time, theme, image_path, is_canceled)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isssssssi",
                $host_id,
                $title,
                $description,
                $location,
                $start_time,
                $end_time,
                $theme,
                $image_path,
                $is_canceled
            );

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: ../dashboard.php?msg=" . urlencode("Event created successfully!"));
                exit();
            } else {
                $message = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf6f0; }
        h2 { text-align: center; color: #7a5c61; margin-top: 30px; }
        form { max-width: 600px; margin: 30px auto; padding: 20px; background-color: #ffffffcc; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; font-size: 1rem; }
        label { font-weight: bold; color: #555; }
        button { padding: 10px 20px; border-radius: 8px; font-size: 1rem; border: none; cursor: pointer; background-color: #a8dadc; color: #1d3557; }
        button:hover { background-color: #89c2d9; }
        .alert { border-radius: 10px; text-align: center; margin-bottom: 20px; }
        a.btn-back { display: inline-block; margin-top: 15px; color: #1d3557; text-decoration: none; font-weight: bold; }
        a.btn-back:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>Create Event</h2>

<div class="container">

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- IMPORTANT: enctype added -->
    <form method="POST" action="create_event.php" enctype="multipart/form-data">

        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Description (optional):</label>
        <textarea name="description" rows="3"></textarea>

        <label>Location:</label>
        <input type="text" name="location">

        <label>Theme (optional):</label>
        <input type="text" name="theme">

        <label>Start Time:</label>
        <input type="datetime-local" name="start_time" required>

        <label>End Time (optional):</label>
        <input type="datetime-local" name="end_time">

        <label>Event Image (optional):</label>
        <input type="file" name="event_image" accept="image/*">

        <button type="submit">Create Event</button>
        <br>
        <a href="../dashboard.php" class="btn-back">&larr; Back to Dashboard</a>

    </form>
</div>

</body>
</html>
