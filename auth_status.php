<?php
session_start();

$response = [];

if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['user'] = [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email']
    ];
} else {
    $response['logged_in'] = false;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
