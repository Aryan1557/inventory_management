<?php
// session_check.php
// Include this AFTER session_start(). db_connection.php only needs to be
// included ONCE per request by whichever page runs first - re-including
// it here (with plain `include`) was opening a second live MySQL
// connection on every page load, which is the most likely cause of
// pages hanging/"loading forever" once your connection limit is hit.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'db_connection.php';

// Not logged in at all -> send to login instead of silently letting
// the page render with no user_id (which produces a broken/blank page).
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT session_token FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

function logout_and_redirect_to_login($reason = null) {
    // Clear session data server-side...
    $_SESSION = [];
    session_unset();
    session_destroy();

    // ...and actually expire the session cookie in the browser too,
    // otherwise the old (now invalid) session ID gets sent right back
    // on the very next request.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    $location = 'login.php';
    if ($reason) {
        $location .= '?msg=' . urlencode($reason);
    }
    header("Location: $location");
    exit();
}

// No such user in the DB anymore (deleted account, bad id, etc.)
if (!$row) {
    logout_and_redirect_to_login();
}

$db_token = (string) ($row['session_token'] ?? '');
$session_token = (string) ($_SESSION['session_token'] ?? '');

// Token missing from this session, missing in the DB (logged out
// elsewhere), or doesn't match (logged in from another device)
if ($session_token === '' || $db_token === '' || !hash_equals($db_token, $session_token)) {
    logout_and_redirect_to_login('another_login');
}

// Session is valid - let the including page continue normally.