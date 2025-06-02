<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

include "../config.php";
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$link = $_POST['link'] ?? '';
$created_at = date('Y-m-d H:i:s');

if (empty($link)) {
    echo json_encode([
        'success' => false,
        'message' => 'Link is required'
    ]);
    exit;
}

$inserted = $db->insert('games', [
    'user_id' => $user_id,
    'link' => $link,
    'created_at' => $created_at
]);

if ($inserted) {
    echo json_encode([
        'success' => true,
        'message' => 'Game successfully created'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database write error'
    ]);
}
exit;
