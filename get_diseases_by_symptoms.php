<?php
include 'db_connection.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['symptoms']) || !is_array($data['symptoms'])) {
    echo json_encode([]);
    exit;
}

$symptoms = $data['symptoms'];
$placeholders = implode(',', array_fill(0, count($symptoms), '?'));

// Prepare query to get diseases that match ANY of the given symptoms
$sql = "
    SELECT DISTINCT d.name AS disease_name
    FROM Diseases d
    JOIN Disease_Symptoms ds ON d.disease_id = ds.disease_id
    JOIN Symptoms s ON s.symptom_id = ds.symptom_id
    WHERE s.name IN ($placeholders)
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Statement preparation failed"]);
    exit;
}

$stmt->bind_param(str_repeat('s', count($symptoms)), ...$symptoms);
$stmt->execute();
$result = $stmt->get_result();

$diseases = [];
while ($row = $result->fetch_assoc()) {
    $diseases[] = $row['disease_name'];
}

echo json_encode($diseases);
?>
