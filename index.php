<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ./login/');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="./src/css/home.css">
    <title>Black Ninja</title>
</head>

<body>
    <header>
        <h1><i class="fas fa-user-ninja icon"></i> Black Ninja</h1>
        <button type="button" class="btn btn-danger" onclick="logout()"><i class="fas fa-sign-out-alt icon"></i>
            Chiqish</button>
    </header>

    <div class="container">
        <button id="createBtn" class="btn"><i class="fas fa-plus icon"></i>Yangi oʻyin yaratish</button>

        <div id="my-games">
            <h3>Mening oʻyinlarim</h3>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./src/js/home.js"></script>
</body>

</html>