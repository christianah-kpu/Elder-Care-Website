<!-- if the user is signed in, display logout in navbar not logged in and sign up -->
<?php
require_once 'db_connection.php';

define('BASE_URL', '/Elder-Care-Website/'); // Base url so pages can use it for links and redirects without worrying about relative paths

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// db_connection.php (which calls session_start) must already be required before this
$username = $_SESSION['username'] ?? '';

?>

<!-- This is the header template. It is meant to hold navigation for regular page -->
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
  </head>
 <body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">Elder Care</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Logged in -->
                    <li class="nav-item">
                        <a style="font-weight: bold;" class="nav-link">Hi, <?php echo htmlspecialchars($username); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= BASE_URL ?>logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Not logged in -->
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= BASE_URL ?>login.php">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= BASE_URL ?>register.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="flex-fill">