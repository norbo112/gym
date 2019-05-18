<?php
    require_once("NWCaptcha.class.php");
    session_start();

    $captcha = new NWCaptcha(230,70, 
        "255,255,255",
        "200,200,200",
        "0,0,0",
        "0,0,0",40,7,12, 3);
    $_SESSION["code"]=$captcha->getCode();
    $captcha->kepKirak();
    //$captcha->writeFonts();
?>