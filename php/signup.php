<?php
require_once 'DBAccess.php';
require_once 'utils.php';
require_once 'DateManager.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
setlocale(LC_ALL, 'it_IT');

session_start();
if (isset($_SESSION['logged_id'])) {
    header('Location: artista.php?id=' . $_SESSION['logged_id']);
    exit();
}

$username = "";
$name = "";
$lastname = "";
$birthplace = "";
$birthdate = "";
$errorMessage = "";

if (isset($_POST['create_account'])) {
    $username = isset($_POST["username"]) ? Sanitizer::sanitizeWord($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? htmlspecialchars($_POST["password"]) : "";
    $name = isset($_POST["name"]) ? Sanitizer::sanitize($_POST["name"]) : "";
    $lastname = isset($_POST["lastname"]) ? Sanitizer::sanitize($_POST["lastname"]) : "";
    $birthplace = isset($_POST["birthplace"]) ? Sanitizer::sanitize($_POST["birthplace"]) : "";
    $birthdate = isset($_POST["birthdate"]) ? Sanitizer::sanitizeDate($_POST["birthdate"]) : "";

    if (!($username && $password && $name && $lastname)) {
        $errorMessage = "<p class=\"error_message\"><em>Parametri non sufficienti</em></p>";
    } else if ($birthdate != "" && Sanitizer::validateDate($birthdate) && ($birthdate < "1900-01-01" || $birthdate >= date("Y-m-d"))) {
        $errorMessage = "<p class=\"error_message\"><em>Inserire una data valida</em></p>";
    } else {
        $connection = new DB\DBAccess();
        if (!$connection->openDBConnection()) {
            header("location: ../php/500.php");
            exit();
        }

        $isUserCreated = $connection->insertNewUser($username, password_hash($password, PASSWORD_BCRYPT), $name, $lastname, "", $birthdate, $birthplace, "", "");

        if ($isUserCreated) {
            $_SESSION['logged_id'] = $connection->getUserLogin($username)[0]["id"];
            $_SESSION['is_admin'] = false;

            header("location: artista.php?id=" . $_SESSION['logged_id']);
            $connection->closeConnection();
            exit();
        } else {
            $errorMessage = "<p class=\"error_message\"><em>Errore nella creazione dell'account</em></p>";
        }
    }
}

$signup = file_get_contents("../templates/signup.html");
$signup = str_replace("{{username}}", $username, $signup);
$signup = str_replace("{{name}}", $name, $signup);
$signup = str_replace("{{lastname}}", $lastname, $signup);
$signup = str_replace("{{birth_place}}", $birthplace, $signup);
$signup = str_replace("{{birth_date}}", $birthdate, $signup);
$signup = str_replace("{{error_message}}", $errorMessage, $signup);
echo ($signup);
