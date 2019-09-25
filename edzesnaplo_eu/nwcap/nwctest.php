<?php
    require_once("NWCaptcha.class.php");
    session_start();

    if(isset($_GET['R']) && isset($_GET['G']) && isset($_GET['B'])) {
        $r = $_GET['R'];
        $g = $_GET['G'];
        $b = $_GET['B'];
    } else {
        $r = 255;
        $g = 255;
        $b = 255;
    }

    $captcha = new NWCaptcha(230,70, 
        "{$r},{$g},{$b}",
        "200,200,200",
        "0,0,0",
        "0,0,0",40,7,12, 3);
    $_SESSION["code"]=$captcha->getCode();
    $captcha->kepKirak();
    //$captcha->writeFonts();
?>