<?php

session_start();
require_once 'db_connection.php';

/* ==========================
   REGISTER
========================== */

if(isset($_POST['register']))
{
    $name     = mysqli_real_escape_string($conn,$_POST['name']);
    $email    = mysqli_real_escape_string($conn,$_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = mysqli_query(
        $conn,
        "SELECT email_id FROM users WHERE email_id='$email'"
    );

    if(mysqli_num_rows($checkEmail) > 0)
    {
        $_SESSION['register_error'] = "Email is already registered";
        $_SESSION['active_form'] = "register";
    }
    else
    {
        $sql = "INSERT INTO users
        (
            name,
            employee_id,
            email_id,
            username,
            password_hash,
            role,
            status
        )
        VALUES
        (
            '$name',
            CONCAT('EMP', FLOOR(RAND()*99999)),
            '$email',
            '$email',
            '$password',
            'User',
            'active'
        )";

        mysqli_query($conn,$sql);
    }

    header("Location: login.php");
    exit();
}

/* ==========================
   LOGIN
========================== */

if(isset($_POST['login']))
{
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query(
        $conn,
        "SELECT * FROM users WHERE email_id='$email'"
    );

    if(mysqli_num_rows($result) > 0)
    {
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password_hash']))
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email_id'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] == 'Admin')
            {
                header("Location: admin_page.php");
            }
            else
            {
                header("Location: user_page.php");
            }

            exit();
        }
    }

    $_SESSION['login_error'] = "Incorrect email or password";
    $_SESSION['active_form'] = "login";

    header("Location: login.php");
    exit();
}
?>