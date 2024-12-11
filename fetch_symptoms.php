<?php
$host = "localhost:3306";
$username = "root";
$password = "";
$dbname = "curepath_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT name FROM symptoms";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="symptom-item">';
        echo '<input type="checkbox" id="' . strtolower($row['name']) . '" name="symptoms" value="' . htmlspecialchars($row['name']) . '">';
        echo '<label for="' . strtolower($row['name']) . '">' . htmlspecialchars($row['name']) . '</label>';
        echo '</div>';
    }
} else {
    echo "<p>No symptoms found</p>";
}

$conn->close();
?>
