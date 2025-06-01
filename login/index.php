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
    $response = ['success' => false, 'title' => 'Error', 'message' => ''];

    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $response['title'] = 'Security Error';
        $response['message'] = 'Invalid CSRF token. Please try again.';
        echo json_encode($response);
        exit;
    }

    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if (empty($username)) {
        $response['title'] = 'Invalid Username';
        $response['message'] = 'Username is required.';
        echo json_encode($response);
        exit;
    }
    if (!preg_match(USERNAME_PATTERN, $username)) {
        $response['title'] = 'Invalid Username';
        $response['message'] = 'Username must be 3-30 characters long and contain only lowercase letters, numbers, or underscores.';
        echo json_encode($response);
        exit;
    }

    if (empty($password)) {
        $response['title'] = 'Invalid Password';
        $response['message'] = 'Password is required.';
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $response['title'] = 'Invalid Password';
        $response['message'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        echo json_encode($response);
        exit;
    }

    $user = $db->select('users', '*', 'username = ?', [$username], 's');

    if ($user && isset($user[0])) {
        $hashedPassword = $user[0]['password'];
        if (password_verify($password, $hashedPassword)) {
            if (strlen($user[0]['name']) > NAME_MAX_LENGTH) {
                $response['title'] = 'Invalid Name';
                $response['message'] = 'Name exceeds maximum length of ' . NAME_MAX_LENGTH . ' characters.';
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
            $response['title'] = 'Success';
            $response['message'] = 'You have successfully logged in!';
            echo json_encode($response);
            exit;
        } else {
            $response['title'] = 'Incorrect Password';
            $response['message'] = 'The password you entered is incorrect.';
            echo json_encode($response);
            exit;
        }
    } else {
        $response['title'] = 'User Not Found';
        $response['message'] = 'No user found with this username.';
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                        <h2 class="text-center mb-4">Login</h2>
                        <form id="loginForm" method="POST">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username"
                                    placeholder="Enter your username">
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Enter your password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button"
                                        onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <?php
                        $redirect_url = isset($_GET['redirect_url']) ? '?redirect_url=' . urlencode($_GET['redirect_url']) : '';
                        ?>
                        <p class="text-center mt-3">
                            Don't have an account? <a href="../signup/<?php echo $redirect_url; ?>">Sign Up</a>
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
                    if (result.title.includes('Username')) {
                        document.getElementById('username').classList.add('error-input');
                    } else if (result.title.includes('Password')) {
                        document.getElementById('password').classList.add('error-input');
                    } else if (result.title.includes('CSRF')) {
                        inputs.forEach(input => input.classList.add('error-input'));
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'There was a problem connecting to the server.'
                });
                console.error('Fetch error:', error);
            }
        });
    </script>
</body>

</html>