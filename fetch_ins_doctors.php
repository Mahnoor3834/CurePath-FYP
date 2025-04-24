<?php
include 'db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$hospital_id = isset($data['hospital_id']) ? $data['hospital_id'] : null;

if (!$hospital_id) {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT d.doctor_id, d.name AS doctor_name, d.speciality_id, s.name AS speciality_name, a.day_of_week, a.time
    FROM Doctors d
    JOIN Speciality s ON d.speciality_id = s.speciality_id
    JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
    JOIN Hospitals h ON dh.hospital_id = h.hospital_id
    LEFT JOIN availability a ON d.doctor_id = a.doctor_id
    WHERE h.hospital_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['doctor_id'];
    if (!isset($doctors[$id])) {
        $doctors[$id] = [
            'doctor_id' => $row['doctor_id'],
            'name' => $row['doctor_name'],
            'speciality' => $row['speciality_name'],
            'speciality_id' => $row['speciality_id'],
            'availability' => [],
        ];
    }

    if ($row['day_of_week'] && $row['time']) {
        $doctors[$id]['availability'][] = $row['day_of_week'] . ' ' . $row['time'];
    }
}

echo json_encode(array_values($doctors));

$stmt->close();
$conn->close();
?>
