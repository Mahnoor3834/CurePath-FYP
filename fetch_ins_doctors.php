<?php

include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$hospital_id = isset($data['hospital_id']) ? $data['hospital_id'] : null;

if (!$hospital_id) {
    echo json_encode([]);
    exit;
}

// Query to fetch doctors by hospital
$query = "
    SELECT d.name, h.name AS hospital, 
           CONCAT(a.day_of_week, ' ', a.time) AS availability
    FROM Doctors d
    JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
    JOIN Hospitals h ON dh.hospital_id = h.hospital_id
    JOIN availability a ON d.doctor_id = a.doctor_id
    WHERE h.hospital_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode($doctors);

$stmt->close();
$conn->close();
?>
