<?php

$host = "localhost:3306";
$username = "root";
$password = "";
$dbname = "curepath_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Decode incoming data
$data = json_decode(file_get_contents("php://input"), true);
$hospital_id = isset($data['hospital_id']) ? $data['hospital_id'] : null;
$specialization = isset($data['specialization']) ? $data['specialization'] : null;

if (!$hospital_id || !$specialization) {
    echo json_encode([]);
    exit;
}

// Query to fetch doctors by hospital and specialization
$query = "
    SELECT d.name, sp.name AS specialization, h.name AS hospital, 
           CONCAT(a.day_of_week, ' ', a.time) AS availability
    FROM Doctors d
    JOIN Speciality sp ON d.speciality_id = sp.speciality_id
    JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
    JOIN Hospitals h ON dh.hospital_id = h.hospital_id
    JOIN availability a ON d.doctor_id = a.doctor_id
    WHERE h.hospital_id = ? AND sp.name = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $hospital_id, $specialization);
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
