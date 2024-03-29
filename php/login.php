<?php

require_once 'DBAccess.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
setlocale(LC_ALL, 'it_IT');

session_start();
if (isset($_SESSION['logged_id'])) {
    header('Location: artista.php?id=' . $_SESSION['logged_id']);
    exit();
}

$errorMessage = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errorMessage = "<p class=\"error_message\"><em>Parametri non sufficienti</em></p>";
    } else {
        $connection = new DB\DBAccess();
        if (!$connection->openDBConnection()) {
            header("location: ../php/500.php");
            exit();
        }

        $potentialUser = $connection->getUserLogin($username);
        $connection->closeConnection();

        if ($potentialUser && password_verify($password, $potentialUser[0]['password'])) {
            $_SESSION['logged_id'] = $potentialUser[0]['id'];
            $_SESSION['is_admin'] = $potentialUser[0]['is_admin'];

            header('Location: artista.php?id=' . $_SESSION['logged_id']);
            exit();
        } else {
            $errorMessage = "<p class=\"error_message\"><em>Credenziali errate</em></p>";
        }
    }
}

$login = file_get_contents("../templates/login.html");
$login = str_replace("{{error_message}}", $errorMessage, $login);
echo ($login);
