<?php
    require_once("adbcuccok.inc.php");

    if(!isset($_SESSION)) {
        session_start();
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        exit;
    }

    if(!isset($_POST['email']) || $_POST['email'] == "") {
        echo "Email címet kötelező megadni";
        exit;
    } else if(!preg_match('/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/',$_POST['email'])) {
        echo "Érvénytelen email cím!";
        exit;
    }

    if(!isset($_POST['pwd']) || $_POST['pwd'] == "") {
        echo "Jelszót kötelező megadni";
        exit;
    }

    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo "Hibás felhasználónév";
        exit;
    }

    sleep(5);

    $biztFelh = $kapcs->real_escape_string($_POST['email']);
    $biztJelszo = $kapcs->real_escape_string($_POST['pwd']);

    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    $leker = "SELECT knev, email, jelszo FROM felhasznalo WHERE email = '{$biztFelh}'";
    $eredmeny = $kapcs->query($leker);
    if(!$eredmeny) {
        echo "Hiba a lekérdezésben ".$kapcs->connect_error();
        exit;
    }

    $sor = $eredmeny->fetch_assoc();
    $dbjelszo = $sor['jelszo'];

    if(!password_verify($biztJelszo, $dbjelszo)) {
        echo "Hibás jelszót küldtél";
        error_log("Hibás jelszót küldtél");
        exit;
    }

    setcookie("felhasznalo",$sor['email']);
    setcookie("nevem", $sor['knev']);
?>