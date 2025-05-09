<?php
include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
$doctor_id = $data['doctor_id'] ?? null;
$rating = $data['rating'] ?? null;
$review = $data['comment'] ?? null;
$created_at = date('Y-m-d H:i:s');

if ($user_id && $doctor_id && $rating && $review) {
    $checkStmt = $conn->prepare("SELECT id FROM doctor_ratings WHERE user_id = ? AND doctor_id = ?");
    $checkStmt->bind_param("ii", $user_id, $doctor_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $updateStmt = $conn->prepare("UPDATE doctor_ratings SET rating = ?, review = ?, created_at = ? WHERE user_id = ? AND doctor_id = ?");
        $updateStmt->bind_param("issii", $rating, $review, $created_at, $user_id, $doctor_id);

        if ($updateStmt->execute()) {
            $avgQuery = "SELECT AVG(rating) AS avg_rating FROM doctor_ratings WHERE doctor_id = ?";
            $avgStmt = $conn->prepare($avgQuery);
            $avgStmt->bind_param("i", $doctor_id);
            $avgStmt->execute();
            $avgResult = $avgStmt->get_result();
            $avgRow = $avgResult->fetch_assoc();
            $avg_rating = round($avgRow['avg_rating'], 2);

            $updateDoctorStmt = $conn->prepare("UPDATE doctors SET avg_rating = ? WHERE doctor_id = ?");
            $updateDoctorStmt->bind_param("di", $avg_rating, $doctor_id);
            $updateDoctorStmt->execute();

            echo json_encode(["status" => "success", "message" => "Review updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update review."]);
        }
    } else {
        $insertStmt = $conn->prepare("INSERT INTO doctor_ratings (user_id, doctor_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->bind_param("iiiss", $user_id, $doctor_id, $rating, $review, $created_at);

        if ($insertStmt->execute()) {
            $avgQuery = "SELECT AVG(rating) AS avg_rating FROM doctor_ratings WHERE doctor_id = ?";
            $avgStmt = $conn->prepare($avgQuery);
            $avgStmt->bind_param("i", $doctor_id);
            $avgStmt->execute();
            $avgResult = $avgStmt->get_result();
            $avgRow = $avgResult->fetch_assoc();
            $avg_rating = round($avgRow['avg_rating'], 2);

            $updateDoctorStmt = $conn->prepare("UPDATE doctors SET avg_rating = ? WHERE doctor_id = ?");
            $updateDoctorStmt->bind_param("di", $avg_rating, $doctor_id);
            $updateDoctorStmt->execute();

            echo json_encode(["status" => "success", "message" => "Review submitted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to submit review."]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing fields"]);
}
?>
