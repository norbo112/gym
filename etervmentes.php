<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $adatok = false; //ebbe tárolom majd a lekért eredményt hiba és result néven
    $hiba = array();
    $hiba["sikeres"] = "";
    $ekeres = false;
    $etervek = array();

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $hiba["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($hiba);
        exit;
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $hiba["hiba"] = "Mások által elmentett Edzéstervek olvasásához ill. mentéséhez adatbázisba, kérlek jelentkezz be!";
        print json_encode($hiba);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $hiba["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($hiba);
        exit;
    }

    $user = mysqli_real_escape_string($mysqli, $_COOKIE["felhasznalo"]);
    $userID = getUserID($user);

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    if(isset($_POST['eterv']) && $_POST['eterv'] != "") {
        $adatok =  json_decode($_POST['eterv']);
    }

    if(isset($_POST['ekeres']) && $_POST['ekeres'] != "") {
        if(strlen($_POST['ekeres']) == 1) {
            //$adatok = json_decode($_POST['ekeres']);
            $adatok = true;
            $ekeres = trim($_POST['ekeres']);
        } else {
            $adatok = false;
            $ekeres = false;
        }    
    }

    if(isset($_POST['userID']) && isset($_POST['eid'])) {
        if(strlen($_POST['eid']) > 5 && strlen($_POST['userID']) > 3) {
            $adatok = false;
        } else {
            $userID = $mysqli->real_escape_string($_POST['userID']);
            $eid = $mysqli->real_escape_string($_POST['eid']);
        }
    }

    if(!$adatok) {
        $hiba['hiba'] = "Nem adtad meg adatokat!";
        print json_encode($hiba);
        exit;
    }

    if($ekeres == 1) {
        $sql = "SELECT userID, eid FROM edzesterv GROUP BY eid";
        if($eredmeny = $mysqli->query($sql)) {
            if($eredmeny->num_rows > 0) {
                while($e = $eredmeny->fetch_assoc()) {
                    $hiba['etervID'][] = $e['userID'];
                    $hiba['etervEID'][] = $e['eid'];
                }
            } else {
                $hiba['ures'] = "Nincsenek adatok a rendszerben";
            }
        } else {
            $hiba['hiba'] = "Sikertelen adatlekérés".$mysqli->error;
        }

        print json_encode($hiba);
        exit;
    } else if($ekeres == 2) {
        $sql = "SELECT edzesnap FROM edzesterv WHERE userID = '{$userID}' AND eid = '{$eid}' GROUP BY edzesnap";
        if($eredmeny = $mysqli->query($sql)) {
            if($eredmeny->num_rows > 0) {
                //itt kellene épitenem a tervem 
                while($e = $eredmeny->fetch_assoc()) {
                    $etervek['eterv']['napok'][] = $e['edzesnap'];
                }
            } else {
                $etervek['ures'] = "Nincsen a tervhez adat";
            }
        } else {
            $etervek['hiba'] = "Sikertelen adatlekérés ".$mysqli->error;
        }
        //vagyis itt küldöm vissza a lekért elmentett edzéstervet
        $sql = "SELECT * FROM edzesterv WHERE userID = '{$userID}' AND eid = '{$eid}'";
        if($eredmeny = $mysqli->query($sql)) {
            if($eredmeny->num_rows > 0) {
                //itt kellene épitenem a tervem 
                while($e = $eredmeny->fetch_assoc()) {
                    $etervek['eterv']['adat'][] = array(
                        'edzesnap' => $e['edzesnap'],
                        'ineve' => $e['izomcsop'],
                        'gyak' => explode('_', $e['gyakorlat']),
                        'sor' => explode('_', $e['sorozat'])
                    );
                }
            } else {
                $etervek['ures'] = "Nincsen a tervhez adat";
            }
        } else {
            $etervek['hiba'] = "Sikertelen adatlekérés ".$mysqli->error;
        }

        print json_encode($etervek);
        exit;
    }

    $randid = random_int(1,1000);
    $sql = "INSERT INTO edzesterv (userID, eid, edzesnap,  izomcsop, gyakorlat, sorozat) VALUES";
    for($i=0; $i<count($adatok); $i++) {
        $adat1 = $mysqli->real_escape_string($adatok[$i]->edzesnap);
        
        for($a=0; $a<count($adatok[$i]->izomcsoport); $a++) {
            $adat2 = $mysqli->real_escape_string($adatok[$i]->izomcsoport[$a]->ineve);
            $gyak = $mysqli->real_escape_string(getGyStr($adatok[$i]->izomcsoport[$a]->gyak));
            $sor = $mysqli->real_escape_string(getGySorStr($adatok[$i]->izomcsoport[$a]->gyak));
            $sql .= "('{$userID}' , '{$randid}', '{$adat1}', '{$adat2}', '{$gyak}', '{$sor}'),";
        }
    }

    $sql = substr($sql, 0, strlen($sql)-1); //ha minden igaz az utolsó vesszőt leveszem

    if($mysqli->query($sql)) {
        $hiba["sikeres"] .= "Sikeres mentés";
    } else {
        $hiba["hiba"] = "Sikertelen mentés ".$mysqli->error;
    }

    print json_encode($hiba);
    exit;

    /*itt módositanom kell a függvényt, mert mostmár a sorozat és ismétlés számokat is tárolnom kell
    na meg új függvény kell, hogy a sorozatot is tároljam a gyakorlatokhoz
    function getGyStr($gyaktomb) {
        $gy = "";
        for($i=0; $i<count($gyaktomb);$i++) {
            $gy .= $gyaktomb[$i]."_";
        }
        $gy = substr($gy, 0, strlen($gy)-1);//leveszem az utolso _ jelet

        return $gy;
    }*/

    function getGyStr($gyaktomb) {
        $gy = "";
        for($i=0; $i<count($gyaktomb);$i++) {
            $gy .= $gyaktomb[$i]->gyaknev."_";
        }
        $gy = substr($gy, 0, strlen($gy)-1);//leveszem az utolso _ jelet

        return $gy;
    }

    function getGySorStr($gyaktomb) {
        //4x12-2_2x30-12_2x12-3 ilyen formában lesz tárolva sqlben
        $gy = "";
        for($i=0; $i<count($gyaktomb);$i++) {
            $gy .= $gyaktomb[$i]->sorozat."x".$gyaktomb[$i]->ismTol."-".$gyaktomb[$i]->ismIg."_";
        }
        $gy = substr($gy, 0, strlen($gy)-1);//leveszem az utolso _ jelet

        return $gy;
    }
?>