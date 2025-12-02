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

$event_id = $_GET['event_id'] ?? null;
$user_id  = $_SESSION['user_id'];

if (!$event_id) {
    header("Location: ../dashboard.php");
    exit();
}

// Check if user is the host
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

// Fetch RSVP details
$stmt = $conn->prepare("
    SELECT r.status, r.responded_at, u.first_name, u.last_name, u.email
    FROM rsvps r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$rsvps = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count RSVP statuses
$count_yes = $count_no = $count_maybe = 0;
foreach ($rsvps as $rsvp) {
    switch (strtolower($rsvp['status'])) {
        case 'yes': $count_yes++; break;
        case 'no': $count_no++; break;
        case 'maybe': $count_maybe++; break;
    }
}

// Build the invite link (you can share this URL)
//$invite_link = "http://localhost/MumboJumbo/events/rsvp.php?event_id=" . $event_id; 
$invite_link = "http://localhost/MumboJumbo-main/events/rsvp.php?event_id=" . $event_id;
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>RSVPs for <?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf6f0; }
        h2 { text-align: center; margin-top: 20px; color: #7a5c61; }
        .container { max-width: 900px; margin-top: 30px; }
        .btn-back { background-color: #fcbfbc; color: #6a4c93; }
        .btn-back:hover { background-color: #f7b7b3; color: #6a4c93; }
        .btn-invite { background-color: #b5ead7; color: #1d3557; margin-bottom: 15px; }
        .btn-invite:hover { background-color: #99e0c5; color: #1d3557; }
        table { background-color: #ffffffcc; border-radius: 8px; }
        th { background-color: #ffd6d6; }
        td { vertical-align: middle; }
        .rsvp-counts { margin-bottom: 20px; font-weight: bold; color: #555; text-align: center; }
        .rsvp-counts span { margin-right: 15px; }

        .btn-send { background-color: #c8e6c9; color: #2e7d32; margin-left: 10px; }
        .btn-send:hover { background-color: #a5d6a7; color: #2e7d32; }


        
    </style>
</head>
<body>
<div class="container">
    <h2>RSVPs for "<?= htmlspecialchars($event['title']) ?>"</h2>

    <div class="text-center mb-3">
        <a href="../dashboard.php" class="btn btn-back">← Back to Dashboard</a>
    </div>

    <!-- Invite Link Button -->
    <div class="text-center mb-3">
        <!--<button class="btn btn-invite" onclick="copyInviteLink()">📎 Copy Invite Link</button>
        <input type="text" id="inviteLink" value="<<?= $invite_link ?>" readonly style="position:absolute; left:-9999px;">-->
        <button class="btn btn-invite" onclick="copyInviteLink()">Copy Invite Link</button>
        <a href="send_invite.php?event_id=<?= $event_id ?>" class="btn btn-send">Send Invitation by Email</a>
        <input type="text" id="inviteLink" value="<?= $invite_link ?>" readonly style="position:absolute; left:-9999px;">
    </div>

    <div class="rsvp-counts">
        <span>✅ Yes: <?= $count_yes ?></span>
        <span>❌ No: <?= $count_no ?></span>
        <span>🤔 Maybe: <?= $count_maybe ?></span>
    </div>

    <?php if (!empty($rsvps)): ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Responded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rsvps as $rsvp): ?>
                    <tr>
                        <td><?= htmlspecialchars($rsvp['first_name']) ?></td>
                        <td><?= htmlspecialchars($rsvp['last_name']) ?></td>
                        <td><?= htmlspecialchars($rsvp['email']) ?></td>
                        <td><?= htmlspecialchars($rsvp['status']) ?></td>
                        <td><?= htmlspecialchars($rsvp['responded_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No RSVPs yet.</p>
    <?php endif; ?>
</div>

<script>
function copyInviteLink() {
    var copyText = document.getElementById("inviteLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");
    alert("Invite link copied to clipboard:\n" + copyText.value);
}
</script>
</body>
</html>
