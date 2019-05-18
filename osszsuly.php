<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $korabbiosszsuly = array(); //ebbe tárolom majd a lekért eredményt hiba és result néven

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $korabbiosszsuly["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($korabbiosszsuly);
        exit;
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $korabbiosszsuly["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($korabbiosszsuly);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $korabbiosszsuly["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($korabbiosszsuly);
        exit;
    }

    $felhasznalo = mysqli_real_escape_string($mysqli, $_COOKIE["felhasznalo"]);

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    //ha létezik a Megnevezes változó, akkor elkászítem hogy visszaadja a script az összsulyt
    if(isset($_POST["Megnevezes"]) && $_POST["Megnevezes"] != "") {
        $megnevezes = mysqli_real_escape_string($mysqli, $_POST["Megnevezes"]);
        $result = getGyakid($mysqli, $megnevezes);
        if(!isset($result["hiba"])) {
            $mentesi = getMentesiDatum($mysqli, $felhasznalo, $result["gyak_id"]);
            
            if(!isset($mentesi["hiba"])) {
                $osszsuly = getOsszsuly($mysqli, $felhasznalo, $mentesi["mentesi"], $result["gyak_id"]);
                if(!isset($osszsuly["hiba"])) {
                    //végeredményben ezt az adatot kell 
                    //elküldenem a edzésnapló napiterv oldának
                    $korabbiosszsuly["result"] = $osszsuly["osszsuly"];
                }
            } else {
                $korabbiosszsuly["hiba"] = $mentesi["hiba"];
            }   
        } else {
            $korabbiosszsuly["hiba"] = $result["hiba"];
        }
        
        //tooltipnek szükség van az utolsóként lementett - elvégzett - gyakorlat sorozatára
        //szóval amugy is léteznie kell a megnevezes változónak, mert az alapján készítem el ezt az 
        // adatot is
        if(isset($_POST["gttp"]) && $_POST["gttp"] == "k") {
            if(!isset($result["hiba"]) && !isset($mentesi["hiba"])) {
                $korabbiosszsuly["sorozat"] = getSorIsmTomb($mysqli, $result["gyak_id"], $mentesi["mentesi"]);
            }
        } 
    } else {
        $korabbiosszsuly["hiba"] = "Nincs megfelelő adat a lekéréshez";
    }

    print json_encode($korabbiosszsuly);
    exit;

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

    function getSorIsmTomb($mysqli, $gyakorlat_id, $mentesi) {
        $sql_sorozat = "SELECT suly,ism FROM sorozat WHERE mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakorlat_id}'";
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
                //$res["idop"][] = $tomb['ismidopont'];
            }
        }

        return $res;
    }
?>