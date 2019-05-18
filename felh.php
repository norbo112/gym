<?php
    require_once("adbcuccok.inc.php");
    $uzenet = array();
    $vanadott = false;

    header("Content-Type: application/json");

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        $uzenet["hiba"] = "Be kell jelentkezned!";
        print json_encode($uzenet);
        exit;
    }

    if(isset($_POST['sajat']) && $_POST['sajat'] != "") {
        $vanadott = true;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        //echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        $uzenet["hiba"] = "Kapcsolodási hiba: ";
        print json_encode($uzenet);
        exit;
    }
    
    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']); 
    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");



    //lekérem a felhasználokat
    if(!$vanadott) {
        $sql = "SELECT vnev, knev, email, megye, varos, cim, iranyitoszam, ".
           "suly, maxfek, maxgugg, letrejott, maxfelhuz, magassag FROM felhasznalo ORDER BY letrejott DESC";
    } else {
        $sql = "SELECT vnev, knev, email, megye, varos, cim, iranyitoszam, ".
        "suly, maxfek, maxgugg, letrejott, maxfelhuz, magassag, megoszt FROM felhasznalo WHERE email = '{$biztFelh}'";
    }
    
    
    if($eredmeny = $kapcs->query($sql)) {
        if($kapcs->affected_rows == 0) {
            http_response_code(400);
            $uzenet["hiba"] = "A felhasználó nem létezik";
        } else {
            while($tomb = $eredmeny->fetch_assoc()) {
                $sajat = array();
                foreach($tomb as $key => $value) {
                    $sajat[$key] = $value;
                    //ide beillesztem hogy hány naplót mentett az adott felhasználó
                    $sajat['mszam'] = getMentettNaploSzam($kapcs, $tomb['email']);
                    if(checkAdmine($biztFelh)) {
                        $sajat['delu'] = "<button type='button' class='btn btn-default btn-xs' onclick='delUsr(\"" .$tomb['email']."\")'><span class='glyphicon glyphicon-remove'></span></button>";
                    } else {
                        $sajat['delu'] = "";
                    }
                }
                $uzenet["felhs"][] = $sajat;
            }
        }
    } else {
        $uzenet["hiba"] = "Hiba történt a lekérdezés közben";
    }

    $kapcs->close();
    print json_encode($uzenet);

    function getMentettNaploSzam($kapcs, $mail) {
        $sql = "SELECT COUNT(naploid) FROM naplo WHERE felhasznalo = '{$mail}' GROUP BY mentesidatum";
        if(!$kapcs->query($sql)) {
            return 0;
        } else {
            return $kapcs->affected_rows;
        }
    }
?>