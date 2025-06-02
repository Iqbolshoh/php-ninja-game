<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: ../');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "../config.php";
$db = new Database();

define('USERNAME_PATTERN', '/^[a-z0-9_]{3,30}$/');
define('NAME_MAX_LENGTH', 100);
define('PASSWORD_MIN_LENGTH', 8);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    header('Content-Type: application/json');
    $response = ['success' => false, 'title' => 'Xato', 'message' => ''];

    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $response['title'] = 'Xavfsizlik Xatosi';
        $response['message'] = 'Noto‘g‘ri CSRF token. Iltimos, qayta urinib ko‘ring.';
        echo json_encode($response);
        exit;
    }

    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if (empty($username)) {
        $response['title'] = 'Noto‘g‘ri Foydalanuvchi Nomi';
        $response['message'] = 'Foydalanuvchi nomi talab qilinadi.';
        echo json_encode($response);
        exit;
    }
    if (!preg_match(USERNAME_PATTERN, $username)) {
        $response['title'] = 'Noto‘g‘ri Foydalanuvchi Nomi';
        $response['message'] = 'Foydalanuvchi nomi 3-30 belgidan iborat bo‘lishi va faqat kichik harflar, raqamlar yoki pastki chiziq belgilarini o‘z ichiga olishi kerak.';
        echo json_encode($response);
        exit;
    }

    if (empty($password)) {
        $response['title'] = 'Noto‘g‘ri Parol';
        $response['message'] = 'Parol talab qilinadi.';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $response['title'] = 'Noto‘g‘ri Parol';
        $response['message'] = 'Parol kamida ' . PASSWORD_MIN_LENGTH . ' belgi uzunlikda bo‘lishi kerak.';
        echo json_encode($response);
        exit;
    }

    $user = $db->select('users', '*', 'username = ?', [$username], 's');

    if ($user && isset($user[0])) {
        $hashedPassword = $user[0]['password'];
        if (password_verify($password, $hashedPassword)) {
            if (strlen($user[0]['name']) > NAME_MAX_LENGTH) {
                $response['title'] = 'Noto‘g‘ri Ism';
                $response['message'] = 'Ism maksimum ' . NAME_MAX_LENGTH . ' belgidan oshib ketdi.';
                echo json_encode($response);
                exit;
            }

            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = [
                'id' => $user[0]['id'],
                'name' => $user[0]['name'],
                'username' => $username
            ];

            $response['success'] = true;
            $response['title'] = 'Muvaffaqiyat';
            $response['message'] = 'Siz muvaffaqiyatli tizimga kirdingiz!';
            echo json_encode($response);
            exit;
        } else {
            $response['title'] = 'Noto‘g‘ri Parol';
            $response['message'] = 'Siz kiritgan parol noto‘g‘ri.';
            echo json_encode($response);
            exit;
        }
    } else {
        $response['title'] = 'Foydalanuvchi Topilmadi';
        $response['message'] = 'Bu foydalanuvchi nomi bilan hech qanday foydalanuvchi topilmadi.';
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizimga Kirish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-toggle:hover {
            cursor: pointer;
        }

        .error-input {
            border-color: #dc3545 !important;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Tizimga Kirish</h2>
                        <form id="loginForm" method="POST">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Foydalanuvchi Nomi</label>
                                <input type="text" class="form-control" name="username" id="username"
                                    placeholder="Foydalanuvchi nomini kiriting">
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Parol</label>
                                <div class="input-group">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Parolni kiriting">
                                    <button class="btn btn-outline-secondary password-toggle" type="button"
                                        onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Kirish</button>
                        </form>
                        <?php
                        $redirect_url = isset($_GET['redirect_url']) ? '?redirect_url=' . urlencode($_GET['redirect_url']) : '';
                        ?>
                        <p class="text-center mt-3">
                            Hisobingiz yo‘qmi? <a href="../signup/<?php echo $redirect_url; ?>">Ro‘yxatdan o‘tish</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => input.classList.remove('error-input'));
            const formData = new FormData(this);
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: result.title,
                        text: result.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() =>
                        window.location.href = new URLSearchParams(window.location.search).get('redirect_url') || '../');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: result.title,
                        text: result.message
                    });
                    if (result.title.includes('Foydalanuvchi Nomi')) {
                        document.getElementById('username').classList.add('error-input');
                    } else if (result.title.includes('Parol')) {
                        document.getElementById('password').classList.add('error-input');
                    } else if (result.title.includes('CSRF')) {
                        inputs.forEach(input => input.classList.add('error-input'));
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tarmoq Xatosi',
                    text: 'Serverga ulanishda muammo yuz berdi.'
                });
                console.error('Fetch xatosi:', error);
            }
        });
    </script>
</body>

</html>