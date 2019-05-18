<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    if(!isset($_COOKIE['felhasznalo'])) {
        echo "Kérlek jelentkezz be a mentés elött";
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        echo "Hiba a felhasználó ellenőrzése során";
        exit;
    }

    $felhasznalo = $_COOKIE["felhasznalo"];
    $mentesiIdopont = date("Y-m-d H:i:s");

    if(!isset($_POST['gyaksik']) || $_POST['gyaksik'] == "") {
        echo "Hiba az adatok feldolgozása közben";
        exit;
    }

    if(isset($_POST["naplonote"]) && $_POST["naplonote"] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .\!\,\?\"]+$/um', $_POST['naplonote'])) {
            echo "A naplóhoz adott leírásban csak betűk és számok szerepelhetnek";
            exit;
        } else {
            $naplonote = $_POST["naplonote"];
        }
    } else {
        $naplonote = "";
    }

    $sajattomb = json_decode($_POST['gyaksik']);
    
    //mentés el9tt a végső verzióban, mindenképp ellenőrizni kell a beküldött adatokat
    //ezeket az ellenörzéseket alaposan átkell dolgoznom majd
    if(mentes($felhasznalo, $mentesiIdopont ,$sajattomb) && saveNaplonote($felhasznalo,$mentesiIdopont,$naplonote)) {
        echo " Sikeres mentés!";
    } else {
        echo " Sikertelen mentés :( ";
    }
    

    //echo "<h2>Ezen időponton lesz mentve a napló: $mentesiIdopont ; a MySQL NOW() függvény is ezt adja</h2><br>";
    function mentes($felhasznalo, $mentesiIdopont, $sajattomb)
    {
        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("Hiba a kapcsolat felépítésében: ".$mysqli->connect_error);
            return false;
        }

        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        //megnézem létezik e a felhasználó
        $secureEmail = $mysqli->real_escape_string($felhasznalo);

        //lekérem a gyakorlat azonositót és megnevezést, amit asoc tömbe tárolom, hogy 
        //a megfelelő id-d adjam a beszuráshoz...
        $gyaktomb = getGyakTomb();
        

        //ciklussal fogom megoldani a mysql-be való adatmentést
        for($i=0; $i<count($sajattomb); $i++) {
            //nyers adat alakítása  mysql tárolásra 2018-09-01 18:33
            //$gyaknev = $mysqli->real_escape_string($sajattomb[$i]->Name);
            $gyakrogzido = $mysqli->real_escape_string($sajattomb[$i]->RogzitesIdopont);
            $gyakmegjegyzes = $mysqli->real_escape_string($sajattomb[$i]->Megjegyzes);

        $sqlNaplo = "INSERT INTO naplo (felhasznalo, mentesidatum, gyakrogzitesiidopont, gyak_id, megjegyzes) VALUES ".
                    "('{$secureEmail}','{$mentesiIdopont}','{$gyakrogzido}', ".
                    "'{$gyaktomb[$sajattomb[$i]->Name]}', ".
                    "'{$gyakmegjegyzes}') ";
            if(!$mysqli->query($sqlNaplo)) {
                error_log("Hiba a napló beszurásakor".$mysqli->error);
                return false;
            } else {
                error_log("Napló sikeresen mentve");
            } 
        }
    
        
        for($i=0; $i<count($sajattomb); $i++) {
            for($k=0; $k<count($sajattomb[$i]->Suly); $k++) {
                $sqlSorozat = "INSERT INTO sorozat (felhasznalo, mentesidatum, gyak_id, suly, ism, ismidopont ) VALUES".
                    "('{$secureEmail}','{$mentesiIdopont}',".
                    "'{$gyaktomb[$sajattomb[$i]->Name]}',".
                    "'{$sajattomb[$i]->Suly[$k]}', '{$sajattomb[$i]->Ism[$k]}', '{$sajattomb[$i]->IsmRogzitesIdopontja[$k]}')";
                if(! ($mysqli->query($sqlSorozat))) {
                    error_log("Sikertelen sorozat tábla frissítés".$mysqli->error);
                    return false;
                } else {
                    error_log("A Sorozat frissítése sikeres volt!");
                }
            }
        }
        return true;
    }

    function getGyakTomb() {
        $gyaktomb = array();
        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("Hiba a kapcsolat felépítésében: ".$mysqli->connect_error);
            return false;
        }

        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        $sql = "SELECT gyak_id, megnevezes FROM gyakorlat";
        if(!$eredmeny = $mysqli->query($sql)) {
            error_log("Hiba a gyakorlatok lekérdezésében: ".$mysqli->error);
        } else {
            if($eredmeny->num_rows == 0) {
                return false;
            } else {
                while($sor = $eredmeny->fetch_assoc()) {
                    $gyaktomb[$sor['megnevezes']] = $sor['gyak_id'];
                }

                $eredmeny->free_result();
                return $gyaktomb;
            }
        }
    }

    function saveNaplonote($felhasznalo,$mentesiIdopont,$naplonote) {
        if($naplonote == "") {
            return;
        }

        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("Hiba a kapcsolat felépítésében: ".$mysqli->connect_error);
            return false;
        }

        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        //naplonote hozzáadása a naplonote táblához
        $secureEmail = $mysqli->real_escape_string($felhasznalo);
        if(isset($naplonote)) {
            $notee = $mysqli->real_escape_string($naplonote);
            $sql = "INSERT INTO naplonote (felhasznalo, mentesidatum, notenotes) ".
                   "VALUES ('{$secureEmail}', '{$mentesiIdopont}', '{$notee}')";

            if(!$mysqli->query($sql)) {
                error_log("Sikertelen naplonote hozzáadás".$mysqli->connect_error);
                return false;
            }
        }

        return true;
    }
?>