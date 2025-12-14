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
        $stmt = $conn->prepare(
            "SELECT * FROM events WHERE host_id = ? ORDER BY start_time ASC"
        );
        $stmt->bind_param("i", $user_id);
        break;

    case 'past':
        $stmt = $conn->prepare(
            "SELECT * FROM events WHERE end_time < NOW() ORDER BY start_time DESC"
        );
        break;

    case 'upcoming':
    default:
        $stmt = $conn->prepare(
            "SELECT * FROM events WHERE start_time >= NOW() ORDER BY start_time ASC"
        );
        break;
}

$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

include 'includes/header.php';
?>

<div class="container mt-2">

    <div class="d-flex justify-content-between align-items-center">
        <?php
            // Normalize view and set title image
            $viewLower = strtolower($view);
            switch ($viewLower) {
                case 'hosted':
                    $titleImage = 'public/img/myhostedevents.png';
                    $altText = 'My Hosted Events';
                    break;
                case 'past':
                    $titleImage = 'public/img/pastevents.png';
                    $altText = 'Past Events';
                    break;
                case 'upcoming':
                default:
                    $titleImage = 'public/img/upcomingevents.png';
                    $altText = 'Upcoming Events';
                    break;
            }
        ?>
        <img src="<?= $titleImage ?>" alt="<?= $altText ?>" style="height: 80px; width: auto;">

        <?php if ($viewLower === 'hosted'): ?>
            <a href="events/create_event.php" class="btn btn-success">
                + Create Event
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($events)): ?>
        <p class="text-center text-muted">No events found.</p>
    <?php else: ?>

        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">

                        <!-- EVENT IMAGE -->
                        <?php if (!empty($event['image_path'])): ?>
                            <img
                                src="<?= htmlspecialchars($event['image_path']) ?>"
                                class="card-img-top"
                                style="height: 200px; object-fit: cover;"
                                alt="Event Image">
                        <?php else: ?>
                            <div
                                class="d-flex align-items-center justify-content-center bg-light text-muted"
                                style="height: 200px;">
                                No Image
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?= htmlspecialchars($event['title']) ?>
                            </h5>

                            <?php if (!empty($event['description'])): ?>
                                <p class="card-text">
                                    <?= htmlspecialchars(substr($event['description'], 0, 120)) ?>
                                    <?= strlen($event['description']) > 120 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>

                            <p class="card-text text-muted mb-1">
                                📍 <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                            </p>

                            <p class="card-text text-muted mb-2">
                                🕒 <?= date("M d, Y g:i A", strtotime($event['start_time'])) ?>
                            </p>

                            <div class="mt-auto">
                                
                                /*<?php if ($event['host_id'] == $user_id): ?>
                                    <a href="events/edit_event.php?event_id=<?= $event['event_id'] ?>"
                                       class="btn btn-warning btn-sm">Edit</a>

                                    <a href="events/delete_event.php?event_id=<?= $event['event_id'] ?>"
                                       onclick="return confirm('Are you sure?');"
                                       class="btn btn-danger btn-sm">Delete</a>
                                <?php else: ?>
                                    <a href="events/rsvp.php?event_id=<?= $event['event_id'] ?>"
                                       class="btn btn-info btn-sm">RSVP</a>
                                <?php endif; ?>*/
                                
                                <?php if ($event['host_id'] == $user_id): ?>
                                 <a href="events/edit_event.php?event_id=<?= $event['event_id'] ?>"
                                class="btn btn-warning btn-sm">Edit</a>

                                <a href="events/delete_event.php?event_id=<?= $event['event_id'] ?>"
                                onclick="return confirm('Are you sure?');"
                                class="btn btn-danger btn-sm">Delete</a>

                                <a href="events/event_rsvps.php?event_id=<?= $event['event_id'] ?>"
                                class="btn btn-info btn-sm">View RSVPs</a>
                                <?php else: ?>
                                <a href="events/rsvp.php?event_id=<?= $event['event_id'] ?>"
                               class="btn btn-info btn-sm">RSVP</a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
