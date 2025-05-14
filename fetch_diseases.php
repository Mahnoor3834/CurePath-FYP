<?php
include 'db_connection.php';

$sql = "SELECT name FROM diseases";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="disease-item">';
        echo '<input type="checkbox" id="' . strtolower($row['name']) . '" name="diseases" value="' . htmlspecialchars($row['name']) . '">';
        echo '<label for="' . strtolower($row['name']) . '">' . htmlspecialchars($row['name']) . '</label>';
        echo '</div>';
    }
} else {
    echo "<p>No diseases found</p>";
}

$conn->close();
?>
