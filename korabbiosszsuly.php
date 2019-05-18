<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    //header("Content-Type: application/json");

    $korabbiosszsuly = array(); //ebbe tárolom majd a lekért eredményt hiba és result néven

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

    $felhasznalo = mysqli_real_escape_string($mysqli, $_COOKIE["felhasznalo"]);

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    //teszt jeleggel megnézem müködik e a getGyakid függvényem
    //a webalkalmazásba ezt a GET-et POSTra kell cserélnem, majd az else ágban tömbe tárolom az üzenet
    //ha nincs ez a Megnevezes változó
    if(isset($_GET["Megnevezes"]) && $_GET["Megnevezes"] != "") {
        $megnevezes = mysqli_real_escape_string($mysqli, $_GET["Megnevezes"]);
        $result = getGyakid($mysqli, $megnevezes);
        if(!isset($result["hiba"])) {
            echo "<h1>".$megnevezes." gyak ID-je: ".$result["gyak_id"]."</h1>";
            echo "<br><br>";
            $mentesi = getMentesiDatum($mysqli, $felhasznalo, $result["gyak_id"]);
            
            if(!isset($mentesi["hiba"])) {
                echo "<h1>Legkésöbb rögzített napló dátuma: ".$mentesi["mentesi"]."</h1>";
                $osszsuly = getOsszsuly($mysqli, $felhasznalo, $mentesi["mentesi"], $result["gyak_id"]);
                if(!isset($osszsuly["hiba"])) {
                    //végeredményben ezt az adatot kell 
                    //elküldenem a edzésnapló napiterv oldának
                    echo "<h2>Összesen megmozgatott súly: ".$osszsuly["osszsuly"]." kg</h2>";
                }
            } else {
                echo "<h1>Legkésöbb rögzített napló hiba: ".$mentesi["hiba"]."</h1>";
            }   
        } else {
            echo "<h1>".$megnevezes." hiba történt: ".$result["hiba"]."</h1>";
        }
        
        echo "<br><br>";
        teszt();
    } else {
        teszt();
    }


    //lekérdező függvények
    // gyakorlat id a neve alapján, false az eredmény ha nem sikerült a lekérdezés
    function getGyakid($mysqli, $gyaknev) {
        $res = array();
        $sql = "SELECT gyak_id FROM gyakorlat WHERE megnevezes = '{$gyaknev}'";
        if(!($eredmeny = $mysqli->query($sql))) {
            error_log("Nem sikerült a gyak_id lekérése".$mysqli->error);
            $res["hiba"] = "Nem sikerült a gyak_id lekérése".$mysqli->error;
        } else {
            if($eredmeny->num_rows == 0) {
                $res["hiba"] = "Nincs megfelelő adat";
            } else {
                $e = $eredmeny->fetch_assoc();
                $res["gyak_id"] = $e["gyak_id"];
            }
        }
        return $res;
    }

    //legkésöbb rögzített napló mentési dátuma
    function getMentesiDatum($mysqli, $felhasznalo, $gyakid) {
        $res = array();
        $atualisDatum = date("Y-m-d H:i:s");
        //legutolsó napló mentési dátuma, ami tartalmazza az adott gyakorlatot, csak a legelső mentési dátum kell
        $sql_naplomentesi = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}'".
                            "AND gyak_id = '{$gyakid}' ORDER BY mentesidatum DESC LIMIT 1"; //limit 1 volt
        if(!($eredmeny = $mysqli->query($sql_naplomentesi))) {
            $res["hiba"] = "Probléma a lekérdezésben: ".$mysqli->error;
        } else {
            if($eredmeny->num_rows != 0) {
                $e = $eredmeny->fetch_assoc();
                $res["mentesi"] = $e["mentesidatum"];
            } else {
                $res["hiba"] = "Nincs megfelelő napló";
            }
        }

        return $res;
    }

    //legkésöbb rögzített adatból kiszámított össz megmozgatott suly
    function getOsszsuly($mysqli, $felhasznalo, $mentesi, $gyakid) {
        $res = array();
        $adat = 0;
        $sql = "SELECT suly, ism FROM sorozat WHERE felhasznalo = '{$felhasznalo}' AND ".
                "mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakid}'";
        if(!($eredmeny = $mysqli->query($sql))) {
            $res["hiba"] = "Probléma a lekérdezésben: ".$mysqli->error;
        } else {
            while($e = $eredmeny->fetch_assoc()) {
                $adat += $e["suly"] * $e["ism"];
            }

            $res["osszsuly"] = $adat;
        }

        return $res;
    }

    //csak a teszthez
    function teszt() {
        $glob = $_SERVER["PHP_SELF"];
        echo "<form method='GET' action='$glob'>";
        echo "<label for='Megnevezes'>Megnevezés: </label>";
        echo "<input type='text' name='Megnevezes' id='Megnevezes'/>";
        echo "<button type='submit'>ID lekérése</button>";
        echo "</form>";
    }
?>