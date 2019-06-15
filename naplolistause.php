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
        break;
        case "BEOLVAS":
        break;
        case "DATUMLISTA" :
            if($eredmeny = $naploclass->getMentesiDatum(null)) {
                $adat = $naploclass->getAdatokVissza();
            } else {
                $adat = $naploclass->getHibaUzenet();
            }
            break;
    }
    
    echo json_encode($adat);
    exit;
?>