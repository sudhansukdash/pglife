<?php
require("../includes/database_connect.php");

$full_name = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$college_name = $_POST['college_name'] ?? '';
$gender = $_POST['gender'] ?? '';

// Basic validation
if (empty($full_name) || empty($phone) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "All fields are required!"]);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This email id is already registered with us!"]);
    exit;
}
$stmt->close();

// Insert user
$stmt = $conn->prepare("INSERT INTO users (email, password, full_name, phone, gender, college_name) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $email, $hash, $full_name, $phone, $gender, $college_name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Your account has been created successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Something went wrong while creating account!"]);
}

$stmt->close();
$conn->close();
?>
