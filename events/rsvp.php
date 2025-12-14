<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
//Includes email_helper.php
$email_enabled = file_exists('../includes/email_helper.php');
if ($email_enabled) {
    include '../includes/email_helper.php';
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo "Event not found.";
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
            $stmt = $conn->prepare("UPDATE rsvps SET status=?, responded_at=NOW() WHERE event_id=? AND user_id=?");
            $stmt->bind_param("sii", $status, $event_id, $user_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, status, responded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $event_id, $user_id, $status);
            $stmt->execute();
        }
        $stmt->close();
        $user_rsvp = $status;
        $message = "Your RSVP has been recorded as '".ucfirst($status)."'.";

// Send email notifications to guest and host if email system is configured
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

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="card">
        <h2 class="text-center mb-4">RSVP for <?= htmlspecialchars($event['title']) ?></h2>

        <p><strong>Description:</strong> <?= htmlspecialchars($event['description'] ?? 'No description') ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($event['location'] ?? 'N/A') ?></p>
        <p><strong>Start Time:</strong> <?= htmlspecialchars($event['start_time']) ?></p>
        <p><strong>End Time:</strong> <?= htmlspecialchars($event['end_time'] ?? 'N/A') ?></p>
        <p><strong>Host:</strong> <?= htmlspecialchars($event['host_first'] . ' ' . $event['host_last']) ?></p>

        <?php if ($message) echo "<p class='text-center fw-bold'>$message</p>"; ?>

        <?php if ($user_id): ?>
            <?php if (!$user_rsvp): ?>
                <form method="POST" action="">
                    <div class="text-center mb-3">
                        <button type="submit" name="status" value="yes" class="btn btn-success me-2">Yes</button>
                        <button type="submit" name="status" value="no" class="btn btn-danger me-2">No</button>
                        <button type="submit" name="status" value="maybe" class="btn btn-warning">Maybe</button>
                    </div>
                </form>
            <?php else: ?>
                <p class='text-center fw-bold'>You have RSVPed: <?= ucfirst($user_rsvp) ?></p>
            <?php endif; ?>
            <div class="text-center">
                <a href="event_rsvps.php?event_id=<?= $event_id ?>" class="btn btn-link">&larr; Back to RSVPs</a>
            </div>
        <?php else: ?>
            <p class="text-center">Please <a href="../login.php">log in</a> to RSVP.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<style>
.card {
    background-color: #ffffffcc;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-top: 50px;
}
</style>
