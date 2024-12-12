<?php

$host = "localhost:3306";
$username = "root";
$password = "";
$dbname = "curepath_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Query to fetch all hospitals
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
