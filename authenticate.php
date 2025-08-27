<?php
session_start();
include "db.php";

$role = $_POST['role'];
$email = $_POST['email'];
$password = $_POST['password'];

if ($role == 'student') {
    $sql = "SELECT * FROM students WHERE gmail=? AND password=?";
} elseif ($role == 'staff') {
    $sql = "SELECT * FROM staff WHERE email=? AND password=?";
} else {
    $sql = "SELECT * FROM admin WHERE email=? AND password=?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['role'] = $role;

    if ($role == 'student') {
        $_SESSION['student_name'] = $user['student_name'];
        $_SESSION['reg_no'] = $user['reg_no'];
        $_SESSION['risk'] = $user['risk_level'];
        header("Location: student_dashboard.php");

    } elseif ($role == 'staff') {
        $_SESSION['staff_name'] = $user['staff_name'];
        $_SESSION['department'] = $user['department'];
        header("Location: staff_dashboard.php");

    } else {
        $_SESSION['admin_name'] = $user['admin_name'];
        header("Location: admin_dashboard.php");
    }
    exit();
} else {
    echo "Invalid email or password!";
}
?>
