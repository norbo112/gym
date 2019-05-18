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
    //$mentesiIdopont = date("Y-m-d H:i:s");
    

    //mentés el9tt a végső verzióban, mindenképp ellenőrizni kell a beküldött adatokat
    //ezeket az ellenörzéseket alaposan átkell dolgoznom majd
    if(mentes($felhasznalo, $_POST)) {
            echo " Sikeres mentés!";
    } else {
        echo " Sikertelen mentés :( ";
    }
    

    //echo "<h2>Ezen időponton lesz mentve a napló: $mentesiIdopont ; a MySQL NOW() függvény is ezt adja</h2><br>";
    function mentes($felhasznalo, $adattomb)
    {
        if(!isset($adattomb["gyaknev"]) || $adattomb["gyaknev"]=="") {
            echo "kérlek adj meg valamilyen gyakorlatot! ";
            return false;
        }

        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("Hiba a kapcsolat felépítésében: ".$mysqli->connect_error);
            return false;
        }

        if(isset($adattomb['mentesidatum']) && $adattomb['mentesidatum'] != "") {
            $mentesiIdopont = $mysqli->real_escape_string($adattomb['mentesidatum']);
        }

        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        //megnézem létezik e a felhasználó
        $secureEmail = $mysqli->real_escape_string($felhasznalo);
        $sql = "SELECT azonosito FROM felhasznalo WHERE email = '{$secureEmail}'";
        if(!$mysqli->query($sql)) {
            error_log("Hibás felhasználó");
            echo "hibás felhasználó ";
            return false;
        }

        //lekérem a gyakorlat azonositót
        //echo $adattomb['gyaknev']." gyakorlat mentése";
        $secureGyakNev = $mysqli->real_escape_string($adattomb["gyaknev"]);
        $sql2 = "SELECT gyak_id FROM gyakorlat WHERE megnevezes = '{$secureGyakNev}'";
        if(! ($eredmeny2 = $mysqli->query($sql2))) {
            error_log("Gyakorlat lekérdezés hiba");
            echo "Gyakorlat lekérdezés hiba";
            return false;
        }

        if($eredmeny2->num_rows == 0) {
            error_log("Nincs ilyen gyakorlat");
            return false;
        }

        $eredmeny2_tomb = $eredmeny2->fetch_assoc();
        $gyak_id = $eredmeny2_tomb["azonosito"];

        //berakom a gyakorlatomat a naplóba és majd a hozzátartozó suly ismétlés, ism időpont-ot
        //a sorozat táblába, gyak_id vel és a mentési dátummal és felhasználóval
        //szóval: napló sor beszúrása
        $secureGyakIdopont = $mysqli->real_escape_string($adattomb['gyakrogzido']);

        if(isset($adattomb['notes']) || $adattomb['notes'] != "") {
            $notes = $mysqli->real_escape_string($adattomb['notes']);
        } else {
            $notes = "";
        }

        $sql3 = "INSERT INTO naplo (felhasznalo, mentesidatum, gyakrogzitesiidopont, gyak_id, megjegyzes)".
                "VALUES ('{$secureEmail}','{$mentesiIdopont}','{$secureGyakIdopont}','{$gyak_id}','{$notes}')";
        
        if(!$mysqli->query($sql3)) {
            error_log("Hiba a napló beszurásakor".$mysqli->connect_error);
            echo "Napló beszurása hiba";
            return false;
        } else {
            error_log("Napló frissítve ".$gyak_id." gyakorlattal");
        }

        //beszurom a sorokat a sorozat táblába, a suly,ism, ismidopontokat feldolgozom tömbbe
        //vesszővel elválasztva fogom átadni a tömböket a phpnak, majd str_getcsv-vel fogom visszaalakítani
        $secureSuly = $mysqli->real_escape_string($adattomb["sulytomb"]);
        $secureIsm = $mysqli->real_escape_string($adattomb["ismtomb"]);
        $secureIsmIdo = $mysqli->real_escape_string($adattomb["ismidotomb"]);

        $suly = str_getcsv($secureSuly);
        $ism = str_getcsv($secureIsm);
        $ismido = str_getcsv($secureIsmIdo);

        //talán elég egyszer vegigmenni a tombök értékein, elvileg egyező tömb méretünek kell
        //lennie
        for($i=0; $i< count($suly)-1; $i++) {
            //$ism_rogz_idopont = "{$ismido[0]}-{$ismido[1]}-{$ismido[2]}".
            //                 " {$ismido[3]}:{$ismido[4]}:{$ismido[5]}";
            $sql_sorozat = "INSERT INTO sorozat (felhasznalo, mentesidatum, gyak_id,suly,ism,ismidopont)".
                "VALUES ('{$secureEmail}','{$mentesiIdopont}','{$gyak_id}','{$suly[$i]}','{$ism[$i]}',".
                "'{$ismido[$i]}')";
            if(! ($mysqli->query($sql_sorozat))) {
                error_log("Sikertelen sorozat tábla frissítés");
                return false;
            }
        }

        return true;
    }
?>