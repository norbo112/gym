<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $gyak_mentesi_datumok = array();
    $csvkeresre = array();
    $vandatum = false;
    $deldatum = null;

    /**
     * Lekérem a naplóból az adott felhasználó mentett naplóit
     * vagyis a mentési dátumokat...
     * majd ugyanevvel a php-szkriptel lekérem a dátumokhoz tartozó
     * gyakorlatokat és sorozatokat, mindenzt jsonba adom át
     */

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $gyak_mentesi_datumok["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($gyak_mentesi_datumok);
        exit;
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $gyak_mentesi_datumok["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($gyak_mentesi_datumok);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $gyak_mentesi_datumok["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($gyak_mentesi_datumok);
        exit;
    }

    if(isset($_POST["mentesidatum"]) && $_POST["mentesidatum"] != "") {
        $vandatum = $mysqli->real_escape_string($_POST["mentesidatum"]);
    }

    if(isset($_POST["deldatum"]) && $_POST["deldatum"] != "") {
        $deldatum = $mysqli->real_escape_string($_POST["deldatum"]);
    }
    

    $felhasznalo = $mysqli->real_escape_string($_COOKIE["felhasznalo"]);
    $getvolt = isset($_GET["csvkeres"]) && $_GET["csvkeres"] == 1;

    $pregt = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/u';
    if(preg_match($pregt, $vandatum)) {
        $sql = "SELECT mentesidatum, gyakrogzitesiidopont ,megnevezes, megjegyzes, gyakorlat.gyak_id as azon, gyakorlat.csoport as csop FROM naplo, gyakorlat WHERE felhasznalo = '{$felhasznalo}' ".
                "AND gyakorlat.gyak_id = naplo.gyak_id AND mentesidatum = '{$vandatum}' ORDER BY gyakrogzitesiidopont ASC";
    } else if($getvolt) {
        //csv mentéshez
        $sql = "SELECT naplo.mentesidatum as mentesidatum, naplo.gyakrogzitesiidopont as gyakrogzitesiidopont, ".
            "naplo.gyak_id,sorozat.mentesidatum, sorozat.gyak_id, sorozat.suly as suly, sorozat.ism as ism, ".
            "sorozat.ismidopont as ismidopont , gyakorlat.gyak_id as gyid,gyakorlat.csoport as csop,gyakorlat.megnevezes as megnevezes FROM naplo ".
            " INNER JOIN sorozat ON sorozat.mentesidatum = naplo.mentesidatum AND sorozat.gyak_id = naplo.gyak_id ".
            "INNER JOIN gyakorlat ON gyakorlat.gyak_id = naplo.gyak_id WHERE naplo.felhasznalo = '{$felhasznalo}'";
    } else if($deldatum != null) {
        $sql = "DELETE FROM naplo WHERE mentesidatum = '{$deldatum}' AND felhasznalo = '{$felhasznalo}'";
    } else {
        $sql = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}' GROUP BY mentesidatum ORDER BY mentesidatum DESC";
    }
    

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");
    

    if(!($eredmeny = $mysqli->query($sql))) {
        $gyak_mentesi_datumok["hiba"] = " Hiba a lekérdezésben ".$mysqli->connect_error;
        error_log("hiba a lekérdezésben ".$mysqli->error);
    } else {
        if(!$deldatum && $eredmeny->num_rows == 0) {
            $gyak_mentesi_datumok["ures"] = "Nincsenek mentett adatok";
        } else {
            if(preg_match($pregt, $vandatum)) {
                //itt lekérem a naplohoz tartozó adatot is
                
                $note = "";
                $sql = "SELECT * FROM naplonote WHERE felhasznalo = '{$felhasznalo}' AND mentesidatum = '{$vandatum}'";
                if($e = $mysqli->query($sql)) {
                    if($e->num_rows > 0) {
                        $erre = $e->fetch_assoc();
                        $gyak_mentesi_datumok["naplonote"] = $erre["notenotes"];
                    } else {
                        $gyak_mentesi_datumok["naplonote"] = "Nincs megjegyzés hozzáfűzve a naplóhoz";
                    }
                } else {
                    $gyak_mentesi_datumok["naplonotehiba"] = "Hiba történt a note lekérésében";
                }
                
                while($tomb = $eredmeny->fetch_assoc()) {
                    $gyak_mentesi_datumok["naplo"][] = array(
                        "gyakrogzido" => $tomb['gyakrogzitesiidopont'],
                        "megnevezes" => $tomb['megnevezes'],
                        "megjegyzes" => $tomb['megjegyzes'],
                        "gycsoport" => $tomb['csop'],
                        "sorozat" => getSorIsmTomb($mysqli, $tomb['azon'],$tomb['mentesidatum'])
                    );
                }
            } else if($getvolt) {
                $file = fopen("tmp_csv.csv","w") or die("sikertelen file nyitás");
                while($tomb = $eredmeny->fetch_assoc()) {
                    fwrite($file, $tomb["mentesidatum"].","
                            .$tomb["gyakrogzitesiidopont"].",".
                            $tomb["megnevezes"].",".
                            $tomb["csop"].",".
                            $tomb["suly"].",".
                            $tomb["ism"].",".
                            $tomb["ismidopont"]."\n");
                } 
                fclose($file);
            } else if($deldatum != null) {
                if($mysqli->affected_rows != 0) {
                    $gyak_mentesi_datumok["torolve"]["naplo"] = "Törölve: ".$mysqli->affected_rows." naplo record";
                    $sqlsorozat = "DELETE FROM sorozat WHERE mentesidatum = '{$deldatum}' AND felhasznalo = '{$felhasznalo}'";
                    if(!$mysqli->query($sqlsorozat)) {
                        $gyak_mentesi_datumok["hiba"] = "Sikertelen sorozat törlés";
                    } else {
                        $gyak_mentesi_datumok["torolve"]["sorozat"] = "Törölve: ".$mysqli->affected_rows." sorozat record";
                    }

                    $sqlnaplonote = "DELETE FROM naplonote WHERE mentesidatum = '{$deldatum}' AND felhasznalo = '{$felhasznalo}'";
                    if(!$mysqli->query($sqlnaplonote)) {
                        $gyak_mentesi_datumok["hiba"] = "Sikertelen naplo törlés";
                    } else {
                        $gyak_mentesi_datumok["torolve"]["note"] = "Törölve: ".$mysqli->affected_rows." naplonote record";
                    }

                } else {
                    $gyak_mentesi_datumok["hiba"] = "Sikertelen napló törlés";
                }
            } else {
                while($tomb = $eredmeny->fetch_assoc()) {
                    $gyak_mentesi_datumok["mentesidatum"][] = $tomb["mentesidatum"];
                }
            }
            
        }
    }

    if($getvolt) {
        //itt valósítom meg hogy kiküldjem a kimenetre a fájlomat amit a kliens letölt
        //alert("Sikeres adatmentés",false);
        csvkiadasa("tmp_csv.csv");
        //echo "<a href='tmp_csv.csv' target='_blank' download>Letöltés</a>";
    } else {
       print json_encode($gyak_mentesi_datumok); 
    }

    function csvkiadasa($filename) {
        if (file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="'.basename($filename).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            flush();
            exit;
        }
    }

    function getSorIsmTomb($mysqli, $gyakorlat_id, $mentesi) {
        $sql_sorozat = "SELECT suly,ism,ismidopont FROM sorozat WHERE mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakorlat_id}'";
        $res = array();

        if(!($eredmeny_sor = $mysqli->query($sql_sorozat))) {
            $res["hiba"] = "Hiba a sorozat lekérdezésben".$mysqli->connect_error;
            error_log("Hiba a sorozat lekérdezésben".$mysqli->connect_error);
        } else if($eredmeny_sor->num_rows == 0) {
            $res["ures"] = "Üres a sorozat tároló, vagy az adatok nem léteznek";
            error_log("Üres a sorozat tároló, vagy az adatok nem léteznek".$mysqli->connect_error);
        } else {
            while($tomb = $eredmeny_sor->fetch_assoc()) {
                $res["suly"][] = $tomb['suly'];
                $res["ism"][] = $tomb['ism'];
                $res["idop"][] = $tomb['ismidopont'];
            }
        }

        return $res;
    }

    function getSorIsmString($mysqli, $gyakorlat_id, $mentesi) {
        $sql_sorozat = "SELECT suly,ism,ismidopont FROM sorozat WHERE mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakorlat_id}'";
        $res = "";

        if(!($eredmeny_sor = $mysqli->query($sql_sorozat))) {
            $res["hiba"] = "Hiba a sorozat lekérdezésben".$mysqli->connect_error;
            error_log("Hiba a sorozat lekérdezésben".$mysqli->connect_error);
        } else if($eredmeny_sor->num_rows == 0) {
            $res["ures"] = "Üres a sorozat tároló, vagy az adatok nem léteznek";
            error_log("Üres a sorozat tároló, vagy az adatok nem léteznek".$mysqli->connect_error);
        } else {
            while($tomb = $eredmeny_sor->fetch_assoc()) {
                $res .= $tomb['suly'].",";
                $res .= $tomb['ism'].",";
                $res .= $tomb['ismidopont'].",";
            }
        }

        return $res;
    }

    function alert($szoveg, $warn) {
        if(!$warn) {
            echo '<div class="alert alert-success alert-dismissible">';
        } else {
            echo '<div class="alert alert-danger alert-dismissible">';
        }
        
        echo '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        if(gettype($szoveg) == "array") {
            echo '<strong>';
            foreach($szoveg as $s) {
                echo "$szoveg[$s] <br>\n";
            }
            echo '</strong>';
        } else {
            echo '<strong>'.$szoveg.' !</strong>';
        }
        
        echo '</div>';
    }
?>