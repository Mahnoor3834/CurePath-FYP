<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$location = $_POST['location'];
$hospital_ids = $_POST['insurance_hospitals']; // array of selected hospital IDs

// Update location
$updateUser = "UPDATE users SET location = ? WHERE id = ?";
$stmt = $conn->prepare($updateUser);
$stmt->bind_param("si", $location, $user_id);
$stmt->execute();
$stmt->close();

// Update hospitals: First delete old ones, then insert new
$conn->query("DELETE FROM user_insurance_hospitals WHERE user_id = $user_id");

$insert = $conn->prepare("INSERT INTO user_insurance_hospitals (user_id, hospital_id) VALUES (?, ?)");
foreach ($hospital_ids as $hospital_id) {
    $insert->bind_param("ii", $user_id, $hospital_id);
    $insert->execute();
}
$insert->close();

$conn->close();
header("Location: dashboard.php");
exit();
