<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';

$email_enabled = file_exists('../includes/email_helper.php');
if ($email_enabled) {
    include '../includes/email_helper.php';
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo "Invalid event ID.";
    exit();
}

// Fetch event info
//$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt = $conn->prepare("SELECT e.*, u.first_name AS host_first, u.last_name AS host_last, u.email AS host_email 
                        FROM events e 
                        JOIN users u ON e.host_id = u.user_id 
                        WHERE e.event_id = ?");

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "Event not found.";
    exit();
}

$message = '';
$user_id = $_SESSION['user_id'] ?? null;

// Handle RSVP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $status = $_POST['status'] ?? '';

    if (!in_array($status, ['yes', 'no', 'maybe'])) {
        $message = "Invalid RSVP selection.";
    } else {
        // Check if user already RSVPed
        $stmt = $conn->prepare("SELECT * FROM rsvps WHERE event_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            // Update existing RSVP
            $stmt = $conn->prepare("UPDATE rsvps SET status=?, responded_at=NOW() WHERE event_id=? AND user_id=?");
            $stmt->bind_param("sii", $status, $event_id, $user_id);
            $stmt->execute();
        } else {
            // Insert new RSVP
            $stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, status, responded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $event_id, $user_id, $status);
            $stmt->execute();
        }
        $stmt->close();
        $user_rsvp = $status; // set immediately so we can show only one message
        $message = "Your RSVP has been recorded as '".ucfirst($status)."'.";

        if ($email_enabled) {
            $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $user = $user_result->fetch_assoc();
            $stmt->close();
            
            $event_details = [
                'title' => $event['title'],
                'location' => $event['location'] ?? 'TBA',
                'start_time' => date('l, F j, Y \a\t g:i A', strtotime($event['start_time']))
            ];
            send_rsvp_confirmation($user['email'], $user['first_name'], $event_details, $status);
            
            $guest_name = $user['first_name'] . ' ' . $user['last_name'];
            $host_name = $event['host_first'] . ' ' . $event['host_last'];
            send_host_rsvp_notification($event['host_email'], $host_name, $guest_name, $event['title'], $status);
        }
    }
}

// Fetch user's current RSVP if logged in
if ($user_id && !isset($user_rsvp)) {
    $stmt = $conn->prepare("SELECT status FROM rsvps WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $user_rsvp = $row['status'] ?? null;
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>RSVP for <?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf6f0; }
        .container { max-width: 600px; margin: 50px auto; background-color: #ffffffcc; padding: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #7a5c61; margin-bottom: 20px; }
        label { font-weight: bold; color: #555; }
        .btn-rsvp { width: 100px; margin: 5px; border-radius: 8px; font-weight: bold; }
        .btn-yes { background-color: #a8dadc; color: #1d3557; }
        .btn-yes:hover { background-color: #89c2d9; }
        .btn-no { background-color: #fcbfbc; color: #6a4c93; }
        .btn-no:hover { background-color: #f7b7b3; }
        .btn-maybe { background-color: #ffe5b4; color: #6a4c93; }
        .btn-maybe:hover { background-color: #ffd699; }
        p.message { text-align: center; font-weight: bold; margin-top: 15px; }
        a.btn-back { display: inline-block; margin-top: 20px; text-decoration: none; color: #1d3557; font-weight: bold; }
        a.btn-back:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>RSVP for <?= htmlspecialchars($event['title']) ?></h2>

    <p><strong>Description:</strong> <?= htmlspecialchars($event['description'] ?? 'No description') ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['location'] ?? 'N/A') ?></p>
    <p><strong>Start Time:</strong> <?= htmlspecialchars($event['start_time']) ?></p>
    <p><strong>End Time:</strong> <?= htmlspecialchars($event['end_time'] ?? 'N/A') ?></p>
    <p><strong>Host:</strong> <?= htmlspecialchars($event['host_first'] . ' ' . $event['host_last']) ?></p>

    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <?php if ($user_id): ?>
        <?php if (!$user_rsvp): ?>
            <form method="POST" action="">
                <div class="text-center">
                    <button type="submit" name="status" value="yes" class="btn btn-rsvp btn-yes">Yes</button>
                    <button type="submit" name="status" value="no" class="btn btn-rsvp btn-no">No</button>
                    <button type="submit" name="status" value="maybe" class="btn btn-rsvp btn-maybe">Maybe</button>
                </div>
            </form>
        <?php else: ?>
            <p class='message'>You have RSVPed: <?= ucfirst($user_rsvp) ?></p>
        <?php endif; ?>
        <div class="text-center">
            <!--<a href="event_rsvps.php?event_id=<?= $event_id ?>" class="btn-back">← Back to RSVPs</a>-->
            <a href="../dashboard.php" class="btn-back">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <p class="message">Please <a href="../login.php">log in</a> to RSVP.</p>
    <?php endif; ?>
</div>
</body>
</html>
