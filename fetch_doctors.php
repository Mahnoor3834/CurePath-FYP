<?php

$host = "localhost:3306";
$username = "root";
$password = "";
$dbname = "curepath_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

    // Get selected symptoms from the POST request
$data = json_decode(file_get_contents("php://input"), true);
$symptoms = $data['symptoms'] ?? [];

if (empty($symptoms)) {
    echo json_encode(['status' => 'error', 'message' => 'No symptoms selected.']);
    exit;
}

file_put_contents("debug.log", "Symptoms: " . implode(", ", $symptoms) . "\n", FILE_APPEND);

    //Query to find doctors based on symptoms
$symptomPlaceholders = implode(',', array_fill(0, count($symptoms), '?'));
$query = "
    SELECT DISTINCT 
        d.name AS doctor_name, 
        sp.name AS speciality_name, 
        h.name AS hospital_name,
        a.day_of_week,
        a.time,
        d.profile_img
    FROM Symptoms s
    JOIN Disease_Symptoms ds ON s.symptom_id = ds.symptom_id
    JOIN Diseases di ON ds.disease_id = di.disease_id
    JOIN Disease_Speciality ds_sp ON di.disease_id = ds_sp.disease_id
    JOIN Speciality sp ON ds_sp.speciality_id = sp.speciality_id
    JOIN Doctors d ON sp.speciality_id = d.speciality_id
    JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
    JOIN Hospitals h ON dh.hospital_id = h.hospital_id
    JOIN Availability a ON d.doctor_id = a.doctor_id AND h.hospital_id = a.hospital_id
    WHERE s.name IN ($symptomPlaceholders)
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param(str_repeat('s', count($symptoms)), ...$symptoms);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution failed: ' . $stmt->error]);
    exit;
}
$result = $stmt->get_result();
$default_image = "default_person.png";
$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'doctor_name' => $row['doctor_name'],
        'speciality_name' => $row['speciality_name'],
        'hospital_name' => $row['hospital_name'],
        'availability' => [
            'day_of_week' => $row['day_of_week'],
            'time' => $row['time']        
        ],
        'profile_img' => !empty($row['profile_img']) ? $row['profile_img'] : $default_image
    ];
}

echo json_encode(['status' => 'success', 'doctors' => $doctors]);

$stmt->close();
$conn->close();
?>
