<?php
include 'db_connection.php';

$sql = "SELECT hospital_id, name FROM Hospitals ORDER BY name";
$result = $conn->query($sql);

$hospitals = [];
while ($row = $result->fetch_assoc()) {
    $hospitals[] = $row;
}

echo json_encode($hospitals);
$conn->close();
?>
