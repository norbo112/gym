<?php
    require_once("adbcuccok.inc.php");

    define("JOG", 1);

    if(!isset($_SESSION)) {
        session_start();
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        echo "Be kell jelentkezned!";
        exit;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        exit;
    }

    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    //ellenőrzöm hogy létezik e a felhasználó, és admin e
$sql = "SELECT admin FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        echo "Nem létezik ilyen felhasználó ".$kapcs->connect_error;
    } else {
        $sor = $eredmeny->fetch_assoc();
        $admine = $sor['admin'];
        $kimenet = "";
        if($admine == JOG) {
            //van joga, igy felépítem a hírküldő felületet;
            //file_get_contents fogom használni
            echo "<h1>Hír beküldése</h1>";
            $kimenet .= file_get_contents("hirkuldo_form.html");
            echo $kimenet;
        } else {
            //itt nem biztos hogy kell bármi tartalmat kiküldeni
            //viszont nem adminisztátorok, vendégként küldhetnek nekem üzenetet
            echo "<h1>Üzenet küldése az adminisztátoroknak</h1>";
            $kimenet .= file_get_contents("uzenetkuldo.html");
            echo $kimenet;
        }
    }

    $kapcs->close();
?>