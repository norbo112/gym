<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $result = array();
    $adat = "asd";
    $randomazonosito;

    if(isset($_POST['etrend']) && $_POST['etrend'] != "") {
        $adat = json_decode($_POST['etrend']);
    } else {
        if(!isset($_POST['lekerlista'])) {
            $result['hiba'] = "Nem küldtél adatokat!";
            print json_encode($result);
            exit;
        }
        
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $result["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($result);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $result["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($result);
        exit;
    }


    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $result["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($result);
        exit;
    }

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    if(isset($_POST['leker']) && $_POST['leker'] == "0") {
        $sql = "SELECT (mentesuid) FROM etrendtabla GROUP BY mentesuid";
        if($eredmeny = $mysqli->query($sql)) {
            if($eredmeny->num_rows > 0) {
                while($e = $eredmeny->fetch_assoc()) {
                    $result['etrendek'][] = $e['mentesuid'];
                }
            } else {
                $result['etrendek_null'] = "Nincs mentett adat";
            }
        } else {
            $result['hiba'] = "Sikertelen adatlekérés:\n".$mysqli->error;
        }

        print json_encode($result);
        exit;
    }
    
    if(isset($_POST['lekerlista']) && $_POST['lekerlista'] != "") {
        $leker_mentesuid = $mysqli->real_escape_string($_POST['lekerlista']);
        $sql = "SELECT * FROM etrendtabla WHERE mentesuid = '{$leker_mentesuid}'";
        if($eredmeny = $mysqli->query($sql)) {
            if($eredmeny->num_rows > 0) {
                while($e = $eredmeny->fetch_assoc()) {
                    $result['etrendeklista'][] = array(
                        "napszak" => $e['napszak'],
                        "etelek" => explode("_", $e['etelek']),
                        "adagok" => explode("_", $e['adagok']),
                        "adatok" => getEtelAdatokNapszakra($mysqli, $e['etelek'], $e['adagok'])
                    );
                }
            } else {
                $result['hiba'] = "Nincsenek a kiválasztott étrendhez tartozó adatok";
            }
            
        } else {
            $result['hiba'] = "Sikertelen adatlekérés".$mysqli->error;
        }

        print json_encode($result);
        exit;
    }

    

    $felhasznalo = $mysqli->real_escape_string($_COOKIE['felhasznalo']);

    $randomazonosito = time();

    $sql = "INSERT INTO etrendtabla (felhasznalo, mentesuid, napszak, etelek, adagok) VALUES";

    for($i=0; $i<count($adat); $i++) {
        $biztNapszak = $mysqli->real_escape_string($adat[$i]->napszak);
        $sql .= "('{$felhasznalo}', '{$randomazonosito}','{$biztNapszak}',";
        $elelem = getEtelnev($adat[$i]->etelek);
        $sql .= "'{$elelem}',";
        $adagok = implode("_",$adat[$i]->etelek_sulya);
        $sql .= "'{$adagok}'),";
    }
    $sql = substr($sql, 0, strlen($sql)-1);

    if($mysqli->query($sql)) {
        $result['ok'] = "Sikeres adatbázis mentés .: ".mysqli_affected_rows($mysqli)." adat hozzáadva";
    } else {
        $result['hiba'] = "Sikertelen mentési kisérlet\n".$mysqli->error;
    }


    print json_encode($result);
    exit;

    function getEtelnev($tomb) {
        $str = "";
        for ($i=0; $i < count($tomb); $i++) { 
            $str .= $tomb[$i]->elelmiszerneve."_";
        }
        $str = substr($str, 0, strlen($str)-1);
        return $str;
    }

    function getEtelAdatokNapszakra($mysqli, $etelek, $adagok) {
        $res = array();
        $etelek_tomb = explode("_", $etelek);
        $adagok_tomb = explode("_", $adagok);
        for($i=0; $i<count($etelek_tomb); $i++) {
            $et = $mysqli->real_escape_string($etelek_tomb[$i]);
            $sql = "SELECT * FROM tapanyagtabla WHERE elelmiszerneve = '{$et}'";
            //$res['etel']['etelnev'][] = $etelek_tomb[$i];
            if($eredmeny = $mysqli->query($sql)) {
                if($eredmeny->num_rows > 0) {
                    $e = $eredmeny->fetch_assoc();
                    //$res['etel']['eteladatok'][] = array(
                    $res[] = array(
                        "kaloria" => $e['kcal'] * $adagok_tomb[$i],
                        "energia" => $e['kj'] * $adagok_tomb[$i],
                        "zsir" => $e['zsir'] * $adagok_tomb[$i],
                        "szenhidrat" => $e['szenhidrat'] * $adagok_tomb[$i],
                        "feherje" => $e['feherje'] * $adagok_tomb[$i],
                        "rost" => $e['rost'] * $adagok_tomb[$i]
                    );
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return $res;
    }
?>