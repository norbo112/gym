<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    if(!$_COOKIE["felhasznalo"]) {
        echo "Kérlek jelentkezz be mielött menteni szeretnél!";
        exit;
    } else if($_COOKIE["felhasznalo"] == "") {
        echo "Ismeretlen adatok, kérlek jelentkezz be, mielött menteni szerentél!";
        exit;
    }

    $felhasznalo = $_COOKIE["felhasznalo"];
    if(isset($_POST["mentesidatum"]) && $_POST["mentesidatum"] != "") {
        $mentesiIdopont = $_POST["mentesidatum"];
    } else {
        $mentesiIdopont = date("Y-m-d H:i:s");
    }
    //s külön lévő naplo notes mentése miatt elöző naplo mentésből eltárolt időt lekérem
    

    //mentés el9tt a végső verzióban, mindenképp ellenőrizni kell a beküldött adatokat
    //ezeket az ellenörzéseket alaposan átkell dolgoznom majd
    //első a legutolso naplonote
    if(isset($_POST["naplonote"]) || $_POST["naplonote"] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .\!\,\?\"]+$/um', $_POST['naplonote'])) {
            echo "A naplóhoz adott leírásban csak betűk és számok szerepelhetnek";
            exit;
        }
    }

    if(mentes($felhasznalo,$mentesiIdopont, $_POST)) {
        echo " Sikeres mentés!";
    } else {
        echo " Sikertelen mentés :( ";
    }

    //echo "<h2>Ezen időponton lesz mentve a napló: $mentesiIdopont ; a MySQL NOW() függvény is ezt adja</h2><br>";
    function mentes($felhasznalo,$mentesiIdopont, $adattomb)
    {
        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("Hiba a kapcsolat felépítésében: ".$mysqli->connect_error);
            return false;
        }

        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        //naplonote hozzáadása a naplonote táblához
        $secureEmail = $mysqli->real_escape_string($felhasznalo);
        if(isset($adattomb["naplonote"]) && $adattomb["naplonote"] != "") {
            $notee = $mysqli->real_escape_string($adattomb["naplonote"]);
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