<?php
session_start();
require("../includes/database_connect.php");

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    $response = array("success" => false, "message" => "Something went wrong!");
    echo json_encode($response);
    return;
}

if (mysqli_num_rows($result) === 0) {
    $response = array("success" => false, "message" => "Login failed! Invalid email or password.");
    echo json_encode($response);
    return;
}

$row = mysqli_fetch_assoc($result);
$stored_hash = $row['password'];

// Case 1: password is already using password_hash() (bcrypt)
if (password_verify($password, $stored_hash)) {
    // Logged in successfully — no conversion needed
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['full_name'] = $row['full_name'];
    $_SESSION['email'] = $row['email'];

    $response = array("success" => true, "message" => "Login successful!");
    echo json_encode($response);
    return;
}

// Case 2: password is old SHA-1 hash
if (sha1($password) === $stored_hash) {
    // ✅ Convert SHA-1 password to password_hash()
    $new_hash = password_hash($password, PASSWORD_DEFAULT);

    $update_sql = "UPDATE users SET password = '$new_hash' WHERE id = {$row['id']}";
    mysqli_query($conn, $update_sql);

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['full_name'] = $row['full_name'];
    $_SESSION['email'] = $row['email'];

    $response = array("success" => true, "message" => "Login successful! (password upgraded)");
    echo json_encode($response);
    return;
}

// Case 3: password doesn't match either format
$response = array("success" => false, "message" => "Login failed! Invalid email or password.");
echo json_encode($response);
