<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
$message = '';

// Get event_id from GET
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    header("Location: ../dashboard.php");
    exit();
}
$event_id = intval($_GET['event_id']);
$user_id = $_SESSION['user_id'];

// Fetch the existing event
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND host_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: ../dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) && !empty($_POST['description']) ? trim($_POST['description']) : null;
    $location    = isset($_POST['location']) ? trim($_POST['location']) : null;
    $theme       = isset($_POST['theme']) && !empty($_POST['theme']) ? trim($_POST['theme']) : null;
    $start_time  = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
    $end_time    = isset($_POST['end_time']) && !empty($_POST['end_time']) ? trim($_POST['end_time']) : null;
    

    if (empty($title) || empty($start_time)) {
        $message = "Please fill in the title and start time.";
    } else {

        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, location=?, start_time=?, end_time=?, theme=? WHERE event_id=? AND host_id=?");
        $stmt->bind_param("sssssssii", $title, $description, $location, $start_time, $end_time, $theme,  $event_id, $user_id);

        if ($stmt->execute()) {
            $message = "Event updated successfully!";
            // Refresh the event data
            $event['title'] = $title;
            $event['description'] = $description;
            $event['location'] = $location;
            $event['theme'] = $theme;
            $event['start_time'] = $start_time;
            $event['end_time'] = $end_time;
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fdf6f0; /* pastel cream */
        }
        h2 {
            text-align: center;
            margin-top: 30px;
            color: #7a5c61; /* muted plum */
        }
        .back-btn {
            display: block;
            max-width: 200px;
            margin: 20px auto;
        }
        form {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffffcc; /* soft white */
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        label {
            font-weight: bold;
            color: #555;
        }
        button {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            border: none;
            cursor: pointer;
        }
        button[type="submit"] {
            background-color: #a8dadc; /* pastel blue */
            color: #1d3557;
        }
        button[type="submit"]:hover {
            background-color: #89c2d9;
        }
        p {
            text-align: center;
            font-weight: bold;
        }
        p.error {
            color: #e63946; /* pastel red */
        }
        p.success {
            color: #457b9d; /* pastel blue-green */
        }
    </style>
</head>
<body>
    <h2>Edit Event</h2>

    <a href="../dashboard.php" class="btn btn-secondary back-btn">← Back to Dashboard</a>

    <?php if (!empty($message)) { 
        $class = strpos($message, 'Error') === 0 ? 'error' : 'success';
        echo "<p class='$class'>$message</p>"; 
    } ?>

    <form method="POST" action="">
        <label>Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($event['title'] ?? '') ?>" required>

        <label>Description (optional):</label>
        <textarea name="description" rows="3"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>

        <label>Location (optional):</label>
        <input type="text" name="location" value="<?= htmlspecialchars($event['location'] ?? '') ?>">

        <label>Theme (optional):</label>
        <input type="text" name="theme" value="<?= htmlspecialchars($event['theme'] ?? '') ?>">

        <label>Start Time:</label>
        <input type="datetime-local" name="start_time" 
               value="<?= !empty($event['start_time']) ? date('Y-m-d\TH:i', strtotime($event['start_time'])) : '' ?>" required>

        <label>End Time (optional):</label>
        <input type="datetime-local" name="end_time" 
               value="<?= !empty($event['end_time']) ? date('Y-m-d\TH:i', strtotime($event['end_time'])) : '' ?>">


        <button type="submit">Update Event</button>
    </form>
</body>
</html>
