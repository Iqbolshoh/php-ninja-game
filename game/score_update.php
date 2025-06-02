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
        'message' => 'Faqat POST usuli ruxsat etiladi'
    ]);
    exit;
}

$game_id = $_POST['game_id'] ?? null;
$user_id = $_SESSION['user']['id'];
$score = (int) ($_POST['score'] ?? 0);
$played_seconds = (int) ($_POST['played_seconds'] ?? 0);

if (!$game_id || !$user_id) {
    exit(json_encode(['success' => false, 'message' => 'Maʼlumotlar noto‘g‘ri']));
}

$existing = $db->select('game_records', '*', 'game_id = ? AND user_id = ?', [$game_id, $user_id], 'ii');

if (!$existing) {
    $inserted = $db->insert('game_records', [
        'game_id' => $game_id,
        'user_id' => $user_id,
        'score' => $score,
        'played_seconds' => $played_seconds
    ]);

    if ($inserted) {
        echo json_encode(['success' => true, 'message' => 'O‘yin muvaffaqiyatli yaratildi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Qo‘shish amalga oshmadi']);
    }
} else {
    $old = $existing[0];
    if ($score > $old['score'] || $score >= $old['score'] && $played_seconds < $old['played_seconds']) {
        $updated = $db->update('game_records', [
            'score' => $score,
            'played_seconds' => $played_seconds
        ], 'id = ?', [$old['id']], 'i');

        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'O‘yin yangilandi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Yangilash amalga oshmadi']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Yangilash kerak emas']);
    }
}
