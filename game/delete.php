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
        'message' => 'Faqat POST usuli qo‘llaniladi'
    ]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID majburiy'
    ]);
    exit;
}

$deleted = $db->delete('games', 'id = ? AND user_id = ?', [$id, $user_id], 'ii');

if ($deleted) {
    echo json_encode([
        'success' => true,
        'message' => 'O‘yin havolasi muvaffaqiyatli o‘chirildi'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'O‘chirishda xatolik. Iltimos, qayta urinib ko‘ring'
    ]);
}
exit;
