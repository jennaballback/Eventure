<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';

$test_results = [];

// TEST 1: Database Connection
function test_database_connection($conn) {
    if ($conn) {
        return ['status' => 'PASS', 'message' => 'Database connected successfully'];
    } else {
        return ['status' => 'FAIL', 'message' => 'Database connection failed'];
    }
}

// TEST 2: Users Table Exists
function test_users_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($result) > 0) {
        return ['status' => 'PASS', 'message' => 'Users table exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'Users table not found'];
    }
}

// TEST 3: Events Table Exists
function test_events_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
    if (mysqli_num_rows($result) > 0) {
        return ['status' => 'PASS', 'message' => 'Events table exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'Events table not found'];
    }
}

// TEST 4: RSVPs Table Exists
function test_rsvps_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'rsvps'");
    if (mysqli_num_rows($result) > 0) {
        return ['status' => 'PASS', 'message' => 'RSVPs table exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'RSVPs table not found'];
    }
}

// TEST 5: Can Insert Test User
function test_insert_user($conn) {
    $test_email = 'test_' . time() . '@test.com';
    $password_hash = password_hash('testpass', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    $first = 'Test'; $last = 'User';
    $stmt->bind_param("ssss", $first, $last, $test_email, $password_hash);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'User insertion works'];
    } else {
        $stmt->close();
        return ['status' => 'FAIL', 'message' => 'User insertion failed: ' . $stmt->error];
    }
}

// TEST 6: Can Insert Test Event
function test_insert_event($conn) {
    $test_email = 'eventtest_' . time() . '@test.com';
    $password_hash = password_hash('testpass', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    $first = 'Event'; $last = 'Tester';
    $stmt->bind_param("ssss", $first, $last, $test_email, $password_hash);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO events (host_id, title, description, location, start_time) VALUES (?, ?, ?, ?, ?)");
    $title = 'Test Event'; $desc = 'Test'; $loc = 'Test Location'; $time = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt->bind_param("issss", $user_id, $title, $desc, $loc, $time);
    
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'Event insertion works'];
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'FAIL', 'message' => 'Event insertion failed: ' . $stmt->error];
    }
}

// TEST 7: Can Insert Test RSVP
function test_insert_rsvp($conn) {
    $test_email = 'rsvptest_' . time() . '@test.com';
    $password_hash = password_hash('testpass', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
    $first = 'RSVP'; $last = 'Tester';
    $stmt->bind_param("ssss", $first, $last, $test_email, $password_hash);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO events (host_id, title, location, start_time) VALUES (?, ?, ?, ?)");
    $title = 'RSVP Test Event'; $loc = 'Test'; $time = date('Y-m-d H:i:s', strtotime('+1 day'));
    $stmt->bind_param("isss", $user_id, $title, $loc, $time);
    $stmt->execute();
    $event_id = $stmt->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, status) VALUES (?, ?, ?)");
    $status = 'yes';
    $stmt->bind_param("iis", $event_id, $user_id, $status);
    
    if ($stmt->execute()) {
        mysqli_query($conn, "DELETE FROM rsvps WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'PASS', 'message' => 'RSVP insertion works'];
    } else {
        mysqli_query($conn, "DELETE FROM events WHERE event_id = $event_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        $stmt->close();
        return ['status' => 'FAIL', 'message' => 'RSVP insertion failed: ' . $stmt->error];
    }
}

// TEST 8: Login.php File Exists
function test_login_file() {
    if (file_exists('login.php')) {
        return ['status' => 'PASS', 'message' => 'login.php exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'login.php not found'];
    }
}

// TEST 9: Register.php File Exists
function test_register_file() {
    if (file_exists('register.php')) {
        return ['status' => 'PASS', 'message' => 'register.php exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'register.php not found'];
    }
}

// TEST 10: Create Event File Exists
function test_create_event_file() {
    if (file_exists('events/create_event.php')) {
        return ['status' => 'PASS', 'message' => 'create_event.php exists'];
    } else {
        return ['status' => 'FAIL', 'message' => 'create_event.php not found'];
    }
}

// Run all tests
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #fdf6f0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #7a5c61; margin-bottom: 30px; }
        .summary { display: flex; justify-content: space-around; margin-bottom: 30px; }
        .summary-box { padding: 20px; border-radius: 8px; text-align: center; flex: 1; margin: 0 10px; }
        .summary-pass { background-color: #d4edda; color: #155724; }
        .summary-fail { background-color: #f8d7da; color: #721c24; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 5px solid; }
        .pass { background-color: #d4edda; border-color: #28a745; }
        .fail { background-color: #f8d7da; border-color: #dc3545; }
        .test-name { font-weight: bold; font-size: 1.1rem; }
        .test-message { margin-top: 5px; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <h1>MumboJumbo Test Suite</h1>
    
    <div class="summary">
        <div class="summary-box summary-pass">
            <h2><?= $passed ?></h2>
            <p>Tests Passed</p>
        </div>
        <div class="summary-box summary-fail">
            <h2><?= $failed ?></h2>
            <p>Tests Failed</p>
        </div>
    </div>

    <h3>Test Results:</h3>
    <?php foreach ($test_results as $test_name => $result): ?>
        <div class="test-result <?= strtolower($result['status']) ?>">
            <div class="test-name">
                [<?= $result['status'] ?>] <?= htmlspecialchars($test_name) ?>
            </div>
            <div class="test-message"><?= htmlspecialchars($result['message']) ?></div>
        </div>
    <?php endforeach; ?>

    <div style="margin-top: 30px; text-align: center;">
        <a href="index.php" class="btn btn-primary">Back to Home</a>
        <button onclick="location.reload()" class="btn btn-secondary">Run Tests Again</button>
    </div>
</div>
</body>
</html>

