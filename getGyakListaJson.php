<?php
    require_once("adbcuccok.inc.php");

    $izomcsoport = array();
    $gyakorlatok = array();

    $jsonresult = array();

    header("Content-Type: application/json;Accept-Charset: utf-8");

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        $jsonresult['hiba'] = "MySQL kapcsolódási hiba".$kapcs->connect_error;
        exit;
    }

    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

    //elöször lekérdezem az izomcsoportokat, majd ezeket optgroup elemekbe helyezem...
    $sql = "SELECT csoport FROM gyakorlat GROUP BY csoport";
    $eredmeny = $kapcs->query($sql);
    if(!$eredmeny) {
        $jsonresult['hiba'] = "Hiba a lekérdezésben";
        exit;
    }

    while($sor = $eredmeny->fetch_assoc()) {
        foreach($sor as $ertek) {
            $izomcsoport[] = $ertek;
        }
    }
    
    for($i = 0; $i<count($izomcsoport); $i++) {
        $sql = "SELECT megnevezes,leiras, videolink, videostartpoz, gyak_id FROM gyakorlat WHERE csoport = '{$izomcsoport[$i]}'";

        if($eredmeny = $kapcs->query($sql)) {
            while($sor = $eredmeny->fetch_assoc()) {
				//org.json miatt változtattam itt, egyébbként $jsonresult[] volt simán
                $jsonresult['gyaksik'][] = array(
                    "gyakId" => (int)$sor['gyak_id'],
                    "izomcsoport" => $izomcsoport[$i],
                    "gyakorlat" => $sor['megnevezes'],
                    "leiras" => " ".$sor['leiras'],
                    "videolink" => $sor['videolink'],
                    "videostartpoz" => (int)$sor['videostartpoz']
                );
            }
        } else {
            $jsonresult['hiba'] = "Hiba az adatbázis elérésében";
        }
    }

    echo json_encode($jsonresult);
    exit;
    
    function getGyakFromCsoport($kapcs, $izomcsop) {
        $sql = "SELECT megnevezes FROM gyakorlat WHERE csoport = '{$izomcsop}'";
        $res = array();
        if(!($e = $kapcs->query($sql))) {
            $res["hiba"] = false;
            return $res;
        } else {
            if($e->num_rows != 0) {
                while($er = $e->fetch_assoc()) {
                    $res["mn"][] = $er["megnevezes"];
                }
            } else {
                $res["hiba"] = false;
            }
            
        }
        return $res;
    }
?>