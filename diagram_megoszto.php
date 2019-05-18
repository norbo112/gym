<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $diagram_partner = array(); //ebbe tárolom majd a lekért eredményt hiba és result néven

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $diagram_partner["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($diagram_partner);
        exit;
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $diagram_partner["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($diagram_partner);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $diagram_partner["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($diagram_partner);
        exit;
    }

    if(!isset($_POST['partner_kero'])) {
        $diagram_partner['hiba'] = "Nem érkezett vezérlő";
        print json_encode($diagram_partner);
        exit;
    } else {
        if($_POST['partner_kero'] != 1) {
            $diagram_partner['hiba'] = "Rossz vezérlő érkezett";
            print json_encode($diagram_partner);
            exit;
        }
    }

    $felhasznalo = mysqli_real_escape_string($mysqli, $_COOKIE["felhasznalo"]);
    $egy = 1;
    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    $sql = "SELECT azonosito, vnev, knev, email FROM felhasznalo WHERE megoszt = '{$egy}'";
    if(!($eredmeny = $mysqli->query($sql))) {
        $diagram_partner['hiba'] = "Adatbázis hiba ".$mysqli->error;
    } else {
        if($eredmeny->num_rows > 0) {
            while($e = $eredmeny->fetch_assoc()) {
                if($felhasznalo == $e['email']) {
                    continue;
                }
                $diagram_partner['partner'][] = array(
                    'pid' => $e['azonosito'],
                    'vneve'=> $e['vnev'],
                    'kneve' => $e['knev']
                );
            }
        } else {
            $diagram_partner["partner"] = "Nincs megosztó";
        }
    }

    print json_encode($diagram_partner);
    exit;
?>