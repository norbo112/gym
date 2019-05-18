<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $result = array();

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $result["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($result);
        exit;
    }

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    /*
    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $result["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($result);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $result["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($result);
        exit;
    }*/

    //elküldöm a fajtákat, hogy legyen egy selectem is a kereső melett
    $sql = "SELECT fajta FROM tapanyagtabla GROUP BY fajta";
    if($eredmeny = $mysqli->query($sql)) {
        if($eredmeny->num_rows > 0) {
            while($e = $eredmeny->fetch_assoc()) {
                $result['tapanyagfajta'][] = $e['fajta'];
            }
        } else {
            $result['tapanyagfajta'] = "";
        }
    } else {
            $result['tapanyagfajta'] = "";
    }

    $sql = "SELECT * FROM tapanyagtabla";
  
    if($eredmeny = $mysqli->query($sql)) {
        if($eredmeny->num_rows > 0) {
            while($e = $eredmeny->fetch_assoc()) {
                $result['tapanyag'][] = array(
                    'elelmiszerneve' => $e['elelmiszerneve'],
                    'fajta' => $e['fajta'],
                    'energia' => $e['kj'],
                    'kaloria' => $e['kcal'],
                    'feherje' => $e['feherje'],
                    'szenhidrat' => $e['szenhidrat'],
                    'zsir' => $e['zsir'],
                    'rost' => $e['rost']
                );
            }
            //$result["ures"] = "Valami hiba van itten";
        } else {
            $result["ures"] = "Nincsenek adatok a rendszerben";
        }
    } else {
        $result["hiba"] = "Probléma merült fel az adatlekérés során ".$mysqli->error;
    }

    print json_encode($result);
    exit;
?>