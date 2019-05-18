<?php
    require_once("adbcuccok.inc.php");

    $hiba = array();

    if(!isset($_POST['ujgyak_nev'])) {
        $hiba[] = "Nem küldtél információt";
        echo "Nem küldtél információt";
        exit;
    }

    if(isset($_POST['ujgyak_nev']) && $_POST['ujgyak_nev'] == "") {
        $hiba[] = "A Gyakorlat neve nem lehet üres";
    } else if(strlen($_POST['ujgyak_nev']) > 100 ) {
        $hiba[] = "Túl hosszú nevet adtál meg!";
    }

    if(isset($_POST['ujgyak_tipus']) && $_POST['ujgyak_tipus'] == "") {
        $hiba[] = "A Gyakorlat típusa nem lehet üres";
    }

    if(isset($_POST['ujgyak_nev']) && $_POST['ujgyak_nev'] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u',$_POST['ujgyak_nev'])) {
            $hiba[] = "A gyakorlat neve csak betűket és számokat tartalmazhat";
        }
    }

    if(isset($_POST['ujgyak_tipus']) && $_POST['ujgyak_tipus'] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u',$_POST['ujgyak_tipus'])) {
            $hiba[] = "A gyakorlat típus neve csak betűket és számokat tartalmazhat";
        }
    }

    if(isset($_POST['ujgyak_leiras']) && $_POST['ujgyak_leiras'] != "") {
        if(!preg_match('/[\w0-9 \-\_\*\,.]+/um',$_POST['ujgyak_leiras'])) {
            $hiba[] = "A leírás számokat és betűket tartalmazhat";
        } else if(strlen($_POST['ujgyak_nev']) > 250 ) {
            $hiba[] = "Túl hosszú leírást adtál meg!";
        }
    }

    //végső ellenőzréz
    if(count($hiba) > 0) {
        foreach($hiba as $h) {
            echo $h."<br>\n";
        }
    } else {
        if(bekuldo($_POST)) {
            echo "Sikeresen hozzáadtam a gyakorlatot a listához";
        } else {
            echo "Nem tudtam felvenni az uj gyakorlatot";
            error_log("Nem tudtam felvenni az uj gyakorlatot");
        }
    }


    function bekuldo($adatok) {
        $bazis = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($bazis->connect_errno) {
            $hiba[] = "MySQL Kapcsolódási hiba".$bazis->connect_error;
            return false;
        }

        $gyakneve = $bazis->real_escape_string($adatok['ujgyak_nev']);
        $gyaktipus = $bazis->real_escape_string($adatok['ujgyak_tipus']);
        $gyakleiras = $bazis->real_escape_string($adatok['ujgyak_leiras']);

        mysqli_query($bazis,"SET NAMES 'UTF8'");
        mysqli_query($bazis,"SET CHARACTER SET 'UTF8'");

        //ellenörzöm hogy volt e már ilyen gyakorlat
        $sql = "SELECT gyak_id FROM gyakorlat WHERE megnevezes = '{$gyakneve}'";
        $eredmeny = $bazis->query($sql);
        if(!$eredmeny) {
            $hiba[] = "Hiba történt a gyakorlat hozzáadása közben (select)";
            error_log("Hiba történt a gyakorlat hozzáadása közben (select)");
            return false;
        } else {
            $sor = $eredmeny->fetch_assoc();
            if(isset($sor['gyak_id']) && $sor['gyak_id'] != "") {
                $hiba[] = "Ilyen gyakorlat már létezik";
                error_log("Ilyen gyakorlat már létezik");
                return false;
            }
        }

        $sql = "INSERT INTO gyakorlat (csoport, megnevezes, leiras) ".
                " VALUES ('{$gyaktipus}','{$gyakneve}','{$gyakleiras}')";
        
        if($bazis->query($sql)) {
            $azon = $bazis->insert_id;
            error_log("{$gyakneve} beszúrva a {$azon} azonosítóval");
            return true;
        } else {
            $hiba[] = "Nem tudtam hozzáadni a gyakorlatot";
            error_log("{$sql} lekerdezés sikertelen");
            return false;
        }
    }
?>