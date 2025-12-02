<?php
// test_suite.php
// Place this file in the root directory of your project
// Access it at: http://localhost/MumboJumbo-main/test_suite.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';

$test_results = [];

// TEST 1: Database Connection
function test_database_connection($conn) {
    if ($conn && mysqli_ping($conn)) {
        return ['status' => 'PASS', 'message' => 'Database connected successfully'];
    } else {
        return ['status' => 'FAIL', 'message' => 'Database connection failed'];
    }
}

// TEST 2: Users Table Exists and Has Data
function test_users_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($result) == 0) {
        return ['status' => 'FAIL', 'message' => 'Users table does not exist'];
    }
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];
    
    if ($count > 0) {
        return ['status' => 'PASS', 'message' => "Users table exists with {$count} user(s)"];
    } else {
        return ['status' => 'FAIL', 'message' => 'Users table exists but has no data. Register a user first.'];
    }
}

// TEST 3: Events Table Exists
function test_events_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
    if (mysqli_num_rows($result) == 0) {
        return ['status' => 'FAIL', 'message' => 'Events table does not exist'];
    }
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM events");
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];
    
    return ['status' => 'PASS', 'message' => "Events table exists with {$count} event(s)"];
}

// TEST 4: RSVPs Table Exists
function test_rsvps_table($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'rsvps'");
    if (mysqli_num_rows($result) == 0) {
        return ['status' => 'FAIL', 'message' => 'RSVPs table does not exist'];
    }
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM rsvps");
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];
    
    return ['status' => 'PASS', 'message' => "RSVPs table exists with {$count} RSVP(s)"];
}

// TEST 5: User Registration Works (check password hashing)
function test_user_registration($conn) {
    $result = mysqli_query($conn, "SELECT password_hash FROM users LIMIT 1");
    if (mysqli_num_rows($result) == 0) {
        return ['status' => 'FAIL', 'message' => 'No users found. Register at least one user.'];
    }
    
    $row = mysqli_fetch_assoc($result);
    $hash = $row['password_hash'];
    
    if (strlen($hash) >= 60 && strpos($hash, '$') !== false) {
        return ['status' => 'PASS', 'message' => 'User passwords are properly hashed'];
    } else {
        return ['status' => 'FAIL', 'message' => 'User passwords may not be hashed correctly'];
    }
}

// TEST 6: Foreign Keys Working (events reference users)
function test_foreign_keys($conn) {
    $result = mysqli_query($conn, "
        SELECT COUNT(*) as count 
        FROM events e 
        LEFT JOIN users u ON e.host_id = u.user_id 
        WHERE u.user_id IS NULL
    ");
    
    $row = mysqli_fetch_assoc($result);
    $orphaned = $row['count'];
    
    if ($orphaned == 0) {
        return ['status' => 'PASS', 'message' => 'All events have valid host references'];
    } else {
        return ['status' => 'FAIL', 'message' => "{$orphaned} event(s) have invalid host_id"];
    }
}

// TEST 7: RSVP Constraints Working
function test_rsvp_constraints($conn) {
    $result = mysqli_query($conn, "
        SELECT COUNT(*) as count 
        FROM rsvps r 
        LEFT JOIN events e ON r.event_id = e.event_id 
        LEFT JOIN users u ON r.user_id = u.user_id 
        WHERE e.event_id IS NULL OR u.user_id IS NULL
    ");
    
    $row = mysqli_fetch_assoc($result);
    $orphaned = $row['count'];
    
    if ($orphaned == 0) {
        return ['status' => 'PASS', 'message' => 'All RSVPs have valid event and user references'];
    } else {
        return ['status' => 'FAIL', 'message' => "{$orphaned} RSVP(s) have invalid references"];
    }
}

// TEST 8: Required PHP Files Exist
function test_required_files() {
    $required_files = [
        'login.php',
        'register.php',
        'dashboard.php',
        'logout.php',
        'events/create_event.php',
        'events/edit_event.php',
        'events/delete_event.php',
        'events/rsvp.php',
        'events/view_events.php',
        'events/event_rsvps.php',
        'includes/db.php'
    ];
    
    $missing = [];
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            $missing[] = $file;
        }
    }
    
    if (empty($missing)) {
        return ['status' => 'PASS', 'message' => 'All required PHP files exist'];
    } else {
        return ['status' => 'FAIL', 'message' => 'Missing files: ' . implode(', ', $missing)];
    }
}

// TEST 9: Database Schema Check
function test_database_schema($conn) {
    $expected_columns = [
        'users' => ['user_id', 'first_name', 'last_name', 'email', 'password_hash', 'created_at'],
        'events' => ['event_id', 'host_id', 'title', 'description', 'location', 'start_time', 'end_time', 'theme', 'is_canceled', 'created_at'],
        'rsvps' => ['rsvp_id', 'event_id', 'user_id', 'status', 'responded_at']
    ];
    
    $schema_issues = [];
    
    foreach ($expected_columns as $table => $columns) {
        $result = mysqli_query($conn, "DESCRIBE $table");
        if (!$result) {
            $schema_issues[] = "Table $table does not exist";
            continue;
        }
        
        $existing_columns = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[] = $row['Field'];
        }
        
        foreach ($columns as $col) {
            if (!in_array($col, $existing_columns)) {
                $schema_issues[] = "$table missing column: $col";
            }
        }
    }
    
    if (empty($schema_issues)) {
        return ['status' => 'PASS', 'message' => 'Database schema is correct'];
    } else {
        return ['status' => 'FAIL', 'message' => implode('; ', $schema_issues)];
    }
}

// TEST 10: Email Configuration Files
function test_email_files() {
    $email_files = [
        'includes/email_config.php',
        'includes/email_helper.php',
        'events/send_invite.php'
    ];
    
    $missing = [];
    foreach ($email_files as $file) {
        if (!file_exists($file)) {
            $missing[] = $file;
        }
    }
    
    if (empty($missing)) {
        return ['status' => 'PASS', 'message' => 'All email integration files exist'];
    } else {
        return ['status' => 'WARN', 'message' => 'Missing email files: ' . implode(', ', $missing) . ' (Optional feature)'];
    }
}

// Run all tests
$test_results['Database Connection'] = test_database_connection($conn);
$test_results['Users Table'] = test_users_table($conn);
$test_results['Events Table'] = test_events_table($conn);
$test_results['RSVPs Table'] = test_rsvps_table($conn);
$test_results['User Registration'] = test_user_registration($conn);
$test_results['Foreign Keys'] = test_foreign_keys($conn);
$test_results['RSVP Constraints'] = test_rsvp_constraints($conn);
$test_results['Required Files'] = test_required_files();
$test_results['Database Schema'] = test_database_schema($conn);
$test_results['Email Integration'] = test_email_files();

$conn->close();

$passed = 0;
$failed = 0;
$warnings = 0;
foreach ($test_results as $result) {
    if ($result['status'] === 'PASS') $passed++;
    elseif ($result['status'] === 'WARN') $warnings++;
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
            max-width: 900px; 
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
        .warn {
            color: orange;
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
        .info-box {
            background-color: #e7f3ff;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
    </style>
</head>
<body>
    <h1>Eventure Test</h1>
    
    <div class="info-box">
        <strong>Note:</strong> This test suite checks your actual project data and files. 
        If tests fail, you need to fix the actual issues in your project.
    </div>
    
    <div class="summary">
        <p>Tests Passed: <?= $passed ?> | Tests Failed: <?= $failed ?> | Warnings: <?= $warnings ?></p>
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
