<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Eventure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Eventure/public/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="<?php echo isset($_SESSION['user_id']) ? '/Eventure/dashboard.php' : '/Eventure/login.php'; ?>">
            <img src="/Eventure/public/img/eventurelogo.png" alt="Eventure Logo" class="logo" style="height:100px;width:auto; margin-left:30px;">
        </a>

        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu for logged-in users -->
                    <li class="nav-item"> <a class="nav-link <?= ($currentView ?? '') === 'upcoming' ? 'active' : '' ?>" href="/Eventure/dashboard.php?view=upcoming">Upcoming Events</a> </li> 
                    <li class="nav-item"> <a class="nav-link <?= ($currentView ?? '') === 'hosted' ? 'active' : '' ?>" href="/Eventure/dashboard.php?view=hosted">My Hosted Events</a> </li> 
                    <li class="nav-item"> <a class="nav-link <?= ($currentView ?? '') === 'past' ? 'active' : '' ?>" href="/Eventure/dashboard.php?view=past">Past Events</a> </li> 
                    <li class="nav-item"><a class="nav-link" href="/Eventure/logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- Menu for guests -->
                    <li class="nav-item"><a class="nav-link" href="/Eventure/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Eventure/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
