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
    <title>Eventure Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px;
        }
        h1 { 
            text-align: center; 
        }
        .summary { 
            text-align: center; 
            margin: 30px 0;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .pass {
            color: green;
            font-weight: bold;
        }
        .fail {
            color: red;
            font-weight: bold;
        }
        .buttons {
            text-align: center;
            margin-top: 30px;
        }
        button {
            padding: 10px 20px;
            font-size: 14px;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>MumboJumbo Test Suite</h1>
    
    <div class="summary">
        <p>Tests Passed: <?= $passed ?> | Tests Failed: <?= $failed ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($test_results as $test_name => $result): ?>
            <tr>
                <td><?= htmlspecialchars($test_name) ?></td>
                <td class="<?= strtolower($result['status']) ?>"><?= $result['status'] ?></td>
                <td><?= htmlspecialchars($result['message']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="buttons">
        <button onclick="location.href='index.php'">Back to Home</button>
        <button onclick="location.reload()">Run Tests Again</button>
    </div>
</body>
</html>
