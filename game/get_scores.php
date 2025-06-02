<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

include '../config.php';
$db = new Database();

$game_id = $_GET['game_id'] ?? null;
if (!$game_id) {
    echo json_encode([
        'status' => false,
        'message' => 'O‘yin ID si majburiy'
    ]);
    exit;
}

$sql = "SELECT 
    u.name, 
    u.username, 
    gr.score, 
    gr.played_seconds
FROM 
    game_records gr
JOIN 
    users u ON gr.user_id = u.id
WHERE 
    gr.game_id = ?";

$scores = $db->executeQuery(
    $sql,
    [$game_id],
    'i'
)->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($scores);
