<?php
// Start session at the beginning
session_start();

include 'db.php';

// Database Connection
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$db = "chat";

$con = mysqli_connect($host, $dbusername, $dbpassword, $db);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$username = "";
$password1 = "";
$email = "";
$errors = array();

// Signup Form
if (isset($_POST["register"])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password1 = mysqli_real_escape_string($con, $_POST['password1']);
    $password2 = mysqli_real_escape_string($con, $_POST['password2']);

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password1)) {
        array_push($errors, "Password is required");
    }
    if ($password1 !== $password2) {
        array_push($errors, "The two passwords do not match");
    }

    if (count($errors) == 0) {
        // Insert plain text password (NO HASHING)
        $sql = "INSERT INTO login (username, password) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $password1);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "You are logged in";
            header("location: home.php");
            exit();
        } else {
            array_push($errors, "Registration failed. Try again.");
        }

        mysqli_stmt_close($stmt);
    }
}

// Login User
if (isset($_POST['login'])) {
    // Ensure session variables are cleared before login
    session_unset();

    $username = trim(mysqli_real_escape_string($con, $_POST['username']));
    $password1 = trim(mysqli_real_escape_string($con, $_POST['password1']));

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password1)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $query = "SELECT * FROM login WHERE username = ? AND password = ?";
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username, $password1);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user) {
                $_SESSION['username'] = $username;
                $_SESSION['success'] = "You are logged in";
                header("location: home.php");
                exit();
            } else {
                array_push($errors, "Wrong username/password combination");
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_start();
    session_unset();  // Clear all session data
    session_destroy(); // Completely end session
    header('location: login.php');
    exit();
}
?>
