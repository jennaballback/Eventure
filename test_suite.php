<?php
// test_suite.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';

$test_results = [];

// TEST FUNCTIONS

function test_database_connection($conn) {
    return $conn
        ? ['status' => 'PASS', 'message' => 'Database connected successfully']
        : ['status' => 'FAIL', 'message' => 'Database connection failed'];
}

function test_users_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    return ($result && mysqli_num_rows($result) > 0)
        ? ['status' => 'PASS', 'message' => 'Users table exists']
        : ['status' => 'FAIL', 'message' => 'Users table not found'];
}

function test_events_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
    return ($result && mysqli_num_rows($result) > 0)
        ? ['status' => 'PASS', 'message' => 'Events table exists']
        : ['status' => 'FAIL', 'message' => 'Events table not found'];
}

function test_rsvps_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'rsvps'");
    return ($result && mysqli_num_rows($result) > 0)
        ? ['status' => 'PASS', 'message' => 'RSVPs table exists']
        : ['status' => 'FAIL', 'message' => 'RSVPs table not found'];
}

function test_insert_user($conn) {
    $email = 'test_' . time() . '@test.com';
    $pass  = password_hash('testpass', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    if (!$stmt) return ['status' => 'FAIL', 'message' => 'Prepare failed for user insert'];

    $first = 'Test'; 
    $last  = 'User';
    $stmt->bind_param("ssss", $first, $last, $email, $pass);

    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'User insertion works'];
    }

    $error = $stmt->error;
    $stmt->close();
    return ['status' => 'FAIL', 'message' => 'User insertion failed: ' . $error];
}

function test_insert_event($conn) {
    // user
    $email = 'event_' . time() . '@test.com';
    $pass  = password_hash('testpass', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    if (!$stmt) return ['status' => 'FAIL', 'message' => 'Prepare failed for event user insert'];

    $first = 'Event'; 
    $last  = 'Tester';
    $stmt->bind_param("ssss", $first, $last, $email, $pass);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // event
    $stmt = $conn->prepare("INSERT INTO events (host_id, title, description, location, start_time) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        return ['status' => 'FAIL', 'message' => 'Prepare failed for event insert'];
    }

    $title = 'Test Event';
    $desc  = 'Test';
    $loc   = 'Test Location';
    $time  = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt->bind_param("issss", $user_id, $title, $desc, $loc, $time);

    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'Event insertion works'];
    }

    $error = $stmt->error;
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    $stmt->close();
    return ['status' => 'FAIL', 'message' => 'Event insertion failed: ' . $error];
}

function test_insert_rsvp($conn) {
    // user
    $email = 'rsvp_' . time() . '@test.com';
    $pass  = password_hash('testpass', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    if (!$stmt) return ['status' => 'FAIL', 'message' => 'Prepare failed for RSVP user insert'];

    $first = 'RSVP'; 
    $last  = 'Tester';
    $stmt->bind_param("ssss", $first, $last, $email, $pass);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // event
    $stmt = $conn->prepare("INSERT INTO events (host_id, title, location, start_time) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        return ['status' => 'FAIL', 'message' => 'Prepare failed for RSVP event insert'];
    }

    $title = 'RSVP Test Event';
    $loc   = 'Test';
    $time  = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt->bind_param("isss", $user_id, $title, $loc, $time);
    $stmt->execute();
    $event_id = $stmt->insert_id;
    $stmt->close();

    // rsvp
    $stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, status) VALUES (?, ?, ?)");
    if (!$stmt) {
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        return ['status' => 'FAIL', 'message' => 'Prepare failed for RSVP insert'];
    }

    $status = 'yes';
    $stmt->bind_param("iis", $event_id, $user_id, $status);

    if ($stmt->execute()) {
        mysqli_query($conn, "DELETE FROM rsvps WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'RSVP insertion works'];
    }

    $error = $stmt->error;
    mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    $stmt->close();
    return ['status' => 'FAIL', 'message' => 'RSVP insertion failed: ' . $error];
}

function test_login_file() {
    return file_exists('login.php')
        ? ['status' => 'PASS', 'message' => 'login.php exists']
        : ['status' => 'FAIL', 'message' => 'login.php not found'];
}

function test_register_file() {
    return file_exists('register.php')
        ? ['status' => 'PASS', 'message' => 'register.php exists']
        : ['status' => 'FAIL', 'message' => 'register.php not found'];
}

function test_create_event_file() {
    return file_exists('events/create_event.php')
        ? ['status' => 'PASS', 'message' => 'create_event.php exists']
        : ['status' => 'FAIL', 'message' => 'create_event.php not found'];
}

//RUN TESTS

$test_results['Database Connection'] = test_database_connection($conn);
$test_results['Users Table'] = test_users_table($conn);
$test_results['Events Table'] = test_events_table($conn);
$test_results['RSVPs Table'] = test_rsvps_table($conn);
$test_results['Insert User'] = test_insert_user($conn);
$test_results['Insert Event'] = test_insert_event($conn);
$test_results['Insert RSVP'] = test_insert_rsvp($conn);
$test_results['Login File'] = test_login_file();
$test_results['Register File'] = test_register_file();
$test_results['Create Event File'] = test_create_event_file();

$conn->close();

$passed = 0;
$failed = 0;

foreach ($test_results as $result) {
    if ($result['status'] === 'PASS') $passed++;
    else $failed++;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MumboJumbo Test Suite</title>
    <style>
        body { background-color: #fdf6f0; font-family: Arial, sans-serif; padding: 20px; }
        h2 { text-align: center; color: #7a5c61; }
        .box {
            max-width: 700px;
            margin: 20px auto;
            background: #ffffffcc;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        .result { margin-bottom: 15px; padding: 10px; border-radius: 6px; }
        .PASS  { background-color: #d4edda; border-left: 5px solid #155724; }
        .FAIL  { background-color: #f8d7da; border-left: 5px solid #721c24; }
        a { color: #1d3557; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>MumboJumbo Test Suite</h2>

<div class="box">
    <p><strong>Total Tests:</strong> <?= $passed + $failed ?></p>
    <p><strong>Passed:</strong> <?= $passed ?></p>
    <p><strong>Failed:</strong> <?= $failed ?></p>

    <h3>Test Results</h3>

    <?php foreach ($test_results as $name => $result): ?>
        <div class="result <?= $result['status'] ?>">
            <strong><?= htmlspecialchars($name) ?>:</strong> <?= $result['status'] ?><br>
            <?= htmlspecialchars($result['message']) ?>
        </div>
    <?php endforeach; ?>

    <p>
        <a href="index.php">Back to Home</a> |
        <a href="test_suite.php">Run Tests Again</a>
    </p>
</div>

</body>
</html>

