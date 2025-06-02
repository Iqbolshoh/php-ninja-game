<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit;
}

include "../config.php";
$db = new Database();

$user_id = $_SESSION['user']['id'];

$sql = "
    SELECT 
        g.id,
        g.link,
        g.created_at,
        COUNT(DISTINCT gr.user_id) AS players_count
    FROM games g 
    LEFT JOIN game_records gr ON g.id = gr.game_id
    WHERE g.user_id = ?
    GROUP BY g.id, g.link, g.created_at
    ORDER BY g.created_at DESC
";

$games = $db->executeQuery($sql, [$user_id], 'i')->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'O‘yinlar muvaffaqiyatli olindi!',
    'data' => $games
]);
exit;
