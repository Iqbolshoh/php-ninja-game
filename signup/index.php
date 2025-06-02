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
define('PASSWORD_PATTERN', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'title' => 'Error', 'message' => ''];

    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $response['title'] = 'Security Error';
        $response['message'] = 'Invalid CSRF token. Please try again.';
        echo json_encode($response);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $response['title'] = 'Invalid Name';
        $response['message'] = 'Name is required.';
        echo json_encode($response);
        exit;
    }
    if (strlen($name) > NAME_MAX_LENGTH) {
        $response['title'] = 'Invalid Name';
        $response['message'] = 'Name must not exceed ' . NAME_MAX_LENGTH . ' characters.';
        echo json_encode($response);
        exit;
    }

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

    if ($password !== $confirm_password) {
        $response['title'] = 'Passwords Do Not Match';
        $response['message'] = 'Passwords are not the same.';
        echo json_encode($response);
        exit;
    }

    $existingUser = $db->select('users', '*', 'username = ?', [$username], 's');
    if (!empty($existingUser)) {
        $response['title'] = 'Username Taken';
        $response['message'] = 'This username is already in use. Please choose another.';
        echo json_encode($response);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $id = $db->insert('users', [
        'name' => $name,
        'username' => $username,
        'password' => $hashedPassword
    ]);

    if ($id) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user'] = [
            'id' => $id,
            'name' => $name,
            'username' => $username
        ];

        $response['success'] = true;
        $response['title'] = 'Success';
        $response['message'] = 'You have successfully signed up!';
        echo json_encode($response);
        exit;
    } else {
        $response['title'] = 'Database Error';
        $response['message'] = 'There was an issue creating the account. Please try again later.';
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
    <title>Sign Up</title>
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
                        <h2 class="text-center mb-4">Sign Up</h2>
                        <form id="signupForm" method="POST">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter your full name" maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Choose a username">
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Create a password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button"
                                        onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" class="form-control"
                                        name="confirm_password" placeholder="Re-enter password">
                                    <button class="btn btn-outline-secondary password-toggle" type="button"
                                        onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                        </form>
                        <?php
                        $redirect_url = isset($_GET['redirect_url']) ? '?redirect_url=' . urlencode($_GET['redirect_url']) : '';
                        ?>
                        <p class="text-center mt-3">
                            Already have an account? <a href="../login/<?php echo $redirect_url; ?>">Login</a>
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
        document.getElementById('signupForm').addEventListener('submit', async function (e) {
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
                    }).then(() => window.location.href = new URLSearchParams(window.location.search).get('redirect_url') || '../');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: result.title,
                        text: result.message
                    });
                    if (result.title.includes('Name')) {
                        document.getElementById('name').classList.add('error-input');
                    } else if (result.title.includes('Username')) {
                        document.getElementById('username').classList.add('error-input');
                    } else if (result.title.includes('Password')) {
                        document.getElementById('password').classList.add('error-input');
                        document.getElementById('confirm_password').classList.add('error-input');
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