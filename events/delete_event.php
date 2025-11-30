<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$event_id = $_GET['event_id'] ?? null;
$user_id  = $_SESSION['user_id'];

if ($event_id) {
    // Validate host
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND host_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);

    if ($stmt->execute()) {
        $msg = "Event deleted successfully!";
    } else {
        $msg = "Error: Could not delete event";
    }

    $stmt->close();
} else {
    $msg = "Invalid event ID.";
}

$conn->close();

header("Location: ../dashboard.php?msg=" . urlencode($msg));
exit();
?>
