<?php
session_start();
include 'db_connection.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([]); 
    exit;
}

$sql = "
    SELECT h.hospital_id, h.name
    FROM hospitals h
    INNER JOIN user_insurance_hospitals uih ON h.hospital_id = uih.hospital_id
    WHERE uih.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$hospitals = [];
while ($row = $result->fetch_assoc()) {
    $hospitals[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($hospitals);
