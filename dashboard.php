<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';
$user_id = $_SESSION['user_id'];

// Fetch all events for the logged-in user
$stmt = $conn->prepare("SELECT * FROM events WHERE host_id = ? ORDER BY start_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Events Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #fdf6f0; /* soft cream background */
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #7a5c61; /* muted plum color */
        }
        .container {
            max-width: 1000px;
        }
        .top-links a {
            margin: 5px;
        }
        .btn-primary {
            background-color: #a8dadc; /* pastel blue */
            border-color: #a8dadc;
            color: #1d3557;
        }
        .btn-primary:hover {
            background-color: #89c2d9;
            border-color: #89c2d9;
        }
        .btn-secondary {
            background-color: #fcbfbc; /* pastel pink */
            border-color: #fcbfbc;
            color: #6a4c93;
        }
        .btn-secondary:hover {
            background-color: #f7b7b3;
            border-color: #f7b7b3;
        }
        .btn-warning {
            background-color: #ffe5b4; /* pastel yellow */
            border-color: #ffe5b4;
            color: #6a4c93;
        }
        .btn-warning:hover {
            background-color: #ffd699;
            border-color: #ffd699;
        }
        .btn-danger {
            background-color: #f4a6a6; /* pastel coral */
            border-color: #f4a6a6;
            color: #5c1a1a;
        }
        .btn-danger:hover {
            background-color: #e78a8a;
            border-color: #e78a8a;
        }
        .btn-info {
            background-color: #b5ead7; /* pastel mint */
            border-color: #b5ead7;
            color: #1d3557;
        }
        .btn-info:hover {
            background-color: #99e0c5;
            border-color: #99e0c5;
        }
        table {
            background-color: #ffffffcc; /* slightly transparent white */
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background-color: #ffd6d6; /* soft pastel header */
        }
        td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Welcome to Your Event Dashboard</h2>

    <div class="text-center mb-3 top-links">
        <a href="events/create_event.php" class="btn btn-primary">Create New Event</a>
        <a href="events/view_events.php" class="btn btn-info">View All Events</a>
        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Location</th>
                <th>Theme</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Created At</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>RSVPs</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($events)) {
            foreach ($events as $event) { ?>
                <tr>
                    <td><?= htmlspecialchars($event['title'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['location'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['theme'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['start_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['end_time'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['created_at'] ?? '') ?></td>
                    <td>
                        <a href="events/edit_event.php?event_id=<?= $event['event_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                    <td>
                        <a href="events/delete_event.php?event_id=<?= $event['event_id'] ?>" onclick="return confirm('Are you sure you want to delete this event?');" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                    <td>
                        <a href="events/event_rsvps.php?event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">View RSVPs</a>
                    </td>
                </tr>
        <?php } } else { ?>
            <tr>
                <td colspan="10" class="text-center">No events found.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
