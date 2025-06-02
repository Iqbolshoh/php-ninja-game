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
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID is required'
    ]);
    exit;
}

$deleted = $db->delete('games', 'id = ? AND user_id = ?', [$id, $user_id], 'ii');

if ($deleted) {
    echo json_encode([
        'success' => true,
        'message' => 'Game link deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error occurred while deleting. Please try again'
    ]);
}
exit;
