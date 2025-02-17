<?php

include 'db_connection.php';

$query = "SELECT hospital_id, name FROM Hospitals";
$result = $conn->query($query);

$hospitals = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = $row;
    }
}

echo json_encode($hospitals);

$conn->close();
?>
