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

$user_id = $_SESSION['user_id'];

// Fetch all events (including those hosted by user or others)
// For simplicity, we assume all events are visible to everyone
$stmt = $conn->prepare("
    SELECT e.*, u.first_name AS host_first, u.last_name AS host_last
    FROM events e
    JOIN users u ON e.host_id = u.user_id
    ORDER BY e.start_time ASC
");
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf6f0; }
        .container { max-width: 1000px; margin-top: 30px; }
        h2 { text-align: center; color: #7a5c61; margin-bottom: 20px; }
        table { background-color: #ffffffcc; border-radius: 8px; }
        th { background-color: #ffd6d6; }
        td { vertical-align: middle; }
        .btn-primary { background-color: #a8dadc; color: #1d3557; }
        .btn-primary:hover { background-color: #89c2d9; color: #1d3557; }
        .btn-info { background-color: #b5ead7; color: #1d3557; }
        .btn-info:hover { background-color: #99e0c5; color: #1d3557; }
    </style>
</head>
<body>
<div class="container">
    <h2>All Events</h2>

    <div class="text-center mb-3">
        <a href="../dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Host</th>
                <th>Description</th>
                <th>Location</th>
                <th>Theme</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>RSVP</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['title']) ?></td>
                    <td><?= htmlspecialchars($event['host_first'] . ' ' . $event['host_last']) ?></td>
                    <td><?= htmlspecialchars($event['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['location'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['theme'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['start_time']) ?></td>
                    <td><?= htmlspecialchars($event['end_time'] ?? '') ?></td>
                    <td>
                        <?php if ($event['host_id'] == $user_id): ?>
                            <a href="event_rsvps.php?event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">View RSVPs</a>
                        <?php else: ?>
                            <a href="rsvp.php?event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">RSVP</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No events found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
