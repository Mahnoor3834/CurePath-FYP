<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';
$speciality = $data['speciality'] ?? '';
$hospital = $data['hospital'] ?? '';

$sql = "SELECT d.doctor_id, d.name AS name, s.name AS speciality, h.name AS hospital, d.profile_img, d.avg_rating AS rating
        FROM Doctors d
        JOIN Speciality s ON d.speciality_id = s.speciality_id
        JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
        JOIN Hospitals h ON dh.hospital_id = h.hospital_id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($name)) {
    $sql .= " AND d.name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

if (!empty($speciality)) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$speciality%";
    $types .= "s";
}

if (!empty($hospital)) {
    $sql .= " AND h.hospital_id = ?";
    $params[] = $hospital;
    $types .= "i";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode($doctors);
$conn->close();
?>
