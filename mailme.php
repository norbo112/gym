<?php
    require_once("adbcuccok.inc.php");

    header("Content-Type: application/json");
    $status = array();

    if(!isset($_POST['email']) || empty($_POST['email'])) {
        $status['hiba'] = "Kérlek add meg az email címedet";
        print(json_encode($status));
        exit;
    }

    if(!isset($_POST['wbuzi']) || empty($_POST['wbuzi'])) {
        $status['hiba'] = "Kérlek add meg az üzenetet";
        print(json_encode($status));
        exit;
    }

    if(strlen($_POST['wbuzi']) > 200) {
        $status['hiba'] = "Túl hosszu szöveget adtál meg";
        print(json_encode($status));
        exit;
    }

    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $status['hiba'] = "Kérlek, érvényes email címet adj meg";
        print(json_encode($status));
        exit;
    }


    $felado = addslashes(trim($_POST['email']));
    $wbuzenet = addslashes(trim($_POST['wbuzi']));
    $wbuzenet = wordwrap($wbuzenet);

$message = "
<html>
<head>
<title>Edzésnapló V2</title>
</head>
<body>
<div style='background-color: black;color:yellow; margin-left:100px; margin-right:100px;'>
<h1 style='text-align:center'>Üdvözlet az edzésnaló alkalmazásból</h1>
<a style='color: white;text-decoration: none' href='norboweb.com/'>Főoldal</a> <a style='color: white;text-decoration: none' href='norboweb.com/gyakorlatok'>Gyakorlatok</a> 
<a style='color: white;text-decoration: none' href='norboweb.com/gyakorlatok'>Felhasznalói oldal</a>
</div>
<div style='background-color: rgb(238, 238, 238);color:black; margin-left:100px; margin-right:100px; padding: 50px;'>
<p>$wbuzenet
</p>
<p>
Küldte: $felado
</p>
</div>
<div style='background-color: black;color:yellow; margin-left:100px; margin-right:100px; padding: 50px;'>
<p>2018 (c) Iglói Norbert
</p>
</div>
</body>
</html>
";

    $cimzett = "jnorbo@gmail.com";
    $targy = "Üzenet az Edzésnapló alkalmazásból";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <webmaster@example.com>' . "\r\n";
    
    if(mail($cimzett,$targy,$message, $headers)) {
        $status['siker'] = "Sikeresen elküldtük az üzenetet";
    } else {
        $status['hiba'] = "Sikertelen üzenetküldés";
    }

    print(json_encode($status));
    exit;
?>