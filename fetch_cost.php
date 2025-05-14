<?php
include 'db_connection.php';

// Decode incoming JSON payload
$data = json_decode(file_get_contents("php://input"), true);
$diseases = $data['diseases'] ?? [];

if (empty($diseases)) {
    echo json_encode(['status' => 'error', 'message' => 'No diseases selected.']);
    exit;
}

// Log selected diseases
file_put_contents("debug.log", "Diseases: " . implode(", ", $diseases) . "\n", FILE_APPEND);

// Prepare placeholders
$placeholders = implode(',', array_fill(0, count($diseases), '?'));

$query = "
    SELECT 
        d.disease_id,
        d.name AS disease_name,
        ec.min_cost,
        ec.max_cost,
        ec.average_cost
    FROM diseases d
    JOIN estimated_cost ec ON d.disease_id = ec.disease_id
    WHERE d.name IN ($placeholders)
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]);
    exit;
}

// Bind the disease names
$stmt->bind_param(str_repeat('s', count($diseases)), ...$diseases);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution failed: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$costData = [];

while ($row = $result->fetch_assoc()) {
    $costData[] = [
        'disease_id' => $row['disease_id'],
        'disease_name' => $row['disease_name'],
        'min_cost' => $row['min_cost'],
        'max_cost' => $row['max_cost'],
        'average_cost' => $row['average_cost']
    ];
}

echo json_encode(['status' => 'success', 'costs' => $costData]);

$stmt->close();
$conn->close();
?>
