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
include '../includes/email_helper.php';

$event_id = $_GET['event_id'] ?? null;
$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

if (!$event_id) {
    header("Location: ../dashboard.php");
    exit();
}

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

$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$host = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_email = trim($_POST['guest_email'] ?? '');
    $guest_name = trim($_POST['guest_name'] ?? '');
    
    if (empty($guest_email) || empty($guest_name)) {
        $message = "Please fill in all fields.";
        $message_type = 'error';
    } elseif (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $message_type = 'error';
    } else {
        $event_details = [
            'title' => $event['title'],
            'description' => $event['description'] ?? 'No description provided',
            'location' => $event['location'] ?? 'TBA',
            'start_time' => date('l, F j, Y \a\t g:i A', strtotime($event['start_time'])),
            'host_name' => $host['first_name'] . ' ' . $host['last_name'],
            'rsvp_link' => "http://localhost/MumboJumbo/events/rsvp.php?event_id=" . $event_id
        ];
        
        if (send_event_invitation($guest_email, $guest_name, $event_details)) {
            $message = "Invitation sent successfully to {$guest_name} ({$guest_email})";
            $message_type = 'success';
        } else {
            $message = "Failed to send email. Please check your email configuration.";
            $message_type = 'error';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Invitation</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 20px;
        }
        h2 { 
            text-align: center; 
        }
        .event-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
        }
        .event-info p {
            margin: 5px 0;
        }
        label { 
            display: block; 
            margin-top: 15px;
            font-weight: bold;
        }
        input { 
            width: 100%; 
            padding: 8px; 
            margin-top: 5px;
            box-sizing: border-box;
        }
        button { 
            padding: 10px 20px; 
            margin-top: 20px;
            cursor: pointer;
            width: 100%;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #ddd;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Send Event Invitation</h2>
    
    <div class="event-info">
        <h4><?= htmlspecialchars($event['title']) ?></h4>
        <p><strong>Location:</strong> <?= htmlspecialchars($event['location'] ?? 'TBA') ?></p>
        <p><strong>Date:</strong> <?= date('l, F j, Y', strtotime($event['start_time'])) ?></p>
        <p><strong>Time:</strong> <?= date('g:i A', strtotime($event['start_time'])) ?></p>
    </div>

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Guest Name:</label>
        <input type="text" name="guest_name" required>
        
        <label>Guest Email:</label>
        <input type="email" name="guest_email" required>
        
        <button type="submit">Send Invitation</button>
    </form>

    <div class="back-link">
        <a href="event_rsvps.php?event_id=<?= $event_id ?>">Back to RSVPs</a>
    </div>
</body>
</html>
