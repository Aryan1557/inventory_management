<!-- <?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "logout";
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query(
    $conn,
    "SELECT session_token
     FROM users
     WHERE user_id='$user_id'"
);

$row = mysqli_fetch_assoc($result);

if (
    !isset($_SESSION['session_token']) ||
    $row['session_token'] != $_SESSION['session_token']
) {
    session_unset();
    session_destroy();
    echo "logout";
} else {
    echo "ok";
}
?> -->