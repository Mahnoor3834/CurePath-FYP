<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require_once "db_connection.php";

$data = json_decode(file_get_contents("php://input"), true);
$doctorId = isset($data['doctor_id']) ? (int) $data['doctor_id'] : 0;

if (!$doctorId) {
    echo json_encode(["status" => "error", "message" => "Doctor ID is required."]);
    exit();
}

$query = "
    SELECT 
        d.doctor_id,
        d.name AS doctor_name,
        sp.name AS speciality_name,
        d.profile_img,
        h.name AS hospital_name,
        h.hospital_id,
        a.day_of_week,
        a.time,
        d.avg_rating
    FROM Doctors d
    JOIN Speciality sp ON d.speciality_id = sp.speciality_id
    JOIN Doctor_Hospital dh ON d.doctor_id = dh.doctor_id
    JOIN Hospitals h ON dh.hospital_id = h.hospital_id
    JOIN Availability a ON d.doctor_id = a.doctor_id AND h.hospital_id = a.hospital_id
    WHERE d.doctor_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

$doctorData = [];
while ($row = $result->fetch_assoc()) {
    $doctorData[] = $row;
}

if (empty($doctorData)) {
    echo json_encode(["status" => "error", "message" => "Doctor not found."]);
    exit();
}

// Extract main doctor info
$doctorDetails = [
    "doctor_id" => $doctorData[0]['doctor_id'],
    "doctor_name" => $doctorData[0]['doctor_name'],
    "speciality_name" => $doctorData[0]['speciality_name'],
    "profile_img" => !empty($doctorData[0]['profile_img']) ? $doctorData[0]['profile_img'] : "default_person.png",
    "rating" => $doctorData[0]['avg_rating']
];

// Extract schedule
$schedule = [];
foreach ($doctorData as $row) {
    $schedule[] = [
        "day_of_week" => $row['day_of_week'],
        "time" => $row['time'],
        "hospital_name" => $row['hospital_name'],
        "hospital_id" => $row['hospital_id']
    ];
}

// Fetch reviews for this doctor
$reviewQuery = $conn->prepare("
    SELECT dr.rating, dr.review, u.fullname AS reviewer_name
    FROM doctor_ratings dr
    JOIN users u ON dr.user_id = u.id
    WHERE dr.doctor_id = ?
    ORDER BY dr.created_at DESC
");
$reviewQuery->bind_param("i", $doctorId);
$reviewQuery->execute();
$reviewResult = $reviewQuery->get_result();

$reviews = [];
while ($row = $reviewResult->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode(["status" => "success", "doctor" => $doctorDetails, "schedule" => $schedule, "reviews" => $reviews]);

$stmt->close();
$conn->close();
?>
