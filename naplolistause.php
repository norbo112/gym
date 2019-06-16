<?php
    require_once("adbcuccok.inc.php");
    require_once("NaploLista.class.php");
    header("Content-Type: application/json; Accept-Charset: UTF-8");

    //majd egyszer android id röggzítése, ellenőrzése,
    //webes felhasználó összekötése az andorid eszközzel
    //jelenleg csak teszt user lesz...
    
    $adat = array();
    $keres = "";

    $naploclass = new NaploLista(ADBSERVER, ADBUSER, ADBPASS, ADBDB);

    if(!$naploclass->initnaplo()) {
        $adat = $naploclass->getHibaUzenet();
        echo json_encode($adat);
        exit;
    }

    //továbbiakban a post ellenörzése, adatok mentése, vagy betöltése
    if(isset($_POST['keres']) && $_POST['keres'] != "") {
        $keres = trim(htmlspecialchars($_POST['keres']));
    }

    switch($keres) {
        case "MENTES":
            if(isset($_POST['job']) && $_POST['job'] != "") {
                if($eredmeny = $naploclass->mentes(null,null,json_decode($_POST['job']))) {
                    $adat["siker"] = "Sikeres mentés!";
                } else {
                    $adat = $naploclass->getHibaUzenet();
                }
            }
            break;
        case "BEOLVAS":
            if(isset($_POST['datum']) && $_POST['datum'] != "") {
                if($eredmeny = $naploclass->getNaploLista($_POST['datum'], null)) {
                    $adat = $naploclass->getAdatokVissza();
                } else {
                    $adat = $naploclass->getHibaUzenet();
                }
            }
            break;
        case "DATUMLISTA" :
            if($eredmeny = $naploclass->getMentesiDatum(null)) {
                $adat = $naploclass->getAdatokVissza();
                //$adat = $naploclass->getTestHibaMentesiDatum(); a java logika jo lett
            } else {
                $adat = $naploclass->getHibaUzenet();
            }
            break;
    }
    
    echo json_encode($adat);
    exit;
?>