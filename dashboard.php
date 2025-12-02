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

// Determine which view to show
$view = $_GET['view'] ?? 'upcoming';

// Build SQL based on selected view
switch ($view) {
    case 'hosted':
        $stmt = $conn->prepare("SELECT * FROM events WHERE host_id = ? ORDER BY start_time ASC");
        $stmt->bind_param("i", $user_id);
        break;
    case 'past':
        $stmt = $conn->prepare("SELECT * FROM events WHERE end_time < NOW() ORDER BY start_time DESC");
        break;
    case 'upcoming':
    default:
        $stmt = $conn->prepare("SELECT * FROM events WHERE start_time >= NOW() ORDER BY start_time ASC");
        break;
}

$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<h2 class="mb-4">
    <?= $view === 'hosted' ? 'My Hosted Events' : ($view === 'past' ? 'Past Events' : 'Upcoming Events') ?>
</h2>

<?php if ($view === 'hosted'): ?>
    <div class="mb-3">
        <a href="create_event.php" class="btn btn-success">Create Event</a>
    </div>
<?php endif; ?>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Location</th>
            <th>Theme</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['title']) ?></td>
                    <td><?= htmlspecialchars($event['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['location'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['theme'] ?? '') ?></td>
                    <td><?= htmlspecialchars($event['start_time']) ?></td>
                    <td><?= htmlspecialchars($event['end_time']) ?></td>
                    <td>
                        <?php if ($event['host_id'] == $user_id): ?>
                            <a href="events/edit_event.php?event_id=<?= $event['event_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="events/delete_event.php?event_id=<?= $event['event_id'] ?>" onclick="return confirm('Are you sure?');" class="btn btn-danger btn-sm">Delete</a>
                        <?php else: ?>
                            <a href="events/rsvp.php?event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">RSVP</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No events found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
