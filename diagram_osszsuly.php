<?php
    require_once("adbcuccok.inc.php");

    if(session_id() == "") {
        session_start();
    }

    header("Content-Type: application/json");

    $diagram_adat = array(); //ebbe tárolom majd a lekért eredményt hiba és result néven
    $volt_partner = false;
    $partner_id = 0;
    $partner_email = "";
    $hibaszoveg = "Ezt a gyakorlatot még egyszersem végezted el";

    $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($mysqli->connect_errno) {
        $diagram_adat["hiba"] = "Hiba a kapcsolat felépítésében: ".$mysqli->connect_error;
        print json_encode($diagram_adat);
        exit;
    }

    if(!isset($_COOKIE["felhasznalo"]) || $_COOKIE["felhasznalo"] == "") {
        $diagram_adat["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($diagram_adat);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        $diagram_adat["hiba"] = "Kérlek jelentkezz be!";
        print json_encode($diagram_adat);
        exit;
    }

    if(isset($_POST['partner_id']) && $_POST['partner_id'] != "") {
        $volt_partner = true;
        $partner_id = $mysqli->real_escape_string($_POST['partner_id']);
        $partner_email = getPartnerEmail($mysqli, $partner_id);
        $hibaszoveg = "A partnered még nem végezte el ezt a gyakorlatot";
        //error_log("partnerem: $partner_email");
    }

    $felhasznalo = mysqli_real_escape_string($mysqli, $_COOKIE["felhasznalo"]);

    $mysqli->query("SET NAMES 'UTF8'");
    $mysqli->query("SET CHARACTER SET 'UTF8'");

    //ha létezik a Megnevezes változó, akkor elkászítem hogy visszaadja a script az összsulyt
    if(isset($_POST["gyak_idd"]) && $_POST["gyak_idd"] != "") {
        //$gyakneve = mysqli_real_escape_string($mysqli, $_POST["megnevezes"]);

        //ha a nevet adják meg akkor visszakeresem a gyakorlat idjét
        if(strlen($_POST['gyak_idd']) > 3) {
            $gyakidd = getGyakid($mysqli, $_POST['gyak_idd']);
            $gyakidd = $gyakidd['gyak_id'];
        } else {
            $gyakidd = mysqli_real_escape_string($mysqli, $_POST["gyak_idd"]);
        }
        
        $mentesi['sajat'] = getMentesiDatum($mysqli, $felhasznalo, $gyakidd);

        if($volt_partner) {
            $mentesi['partner'] = getMentesiDatum($mysqli, $partner_email, $gyakidd);

            if(!isset($mentesi['partner']["hiba"])) {
                for($i=0; $i<count($mentesi['partner']); $i++) {
                    $osszsuly['partner'] = getOsszsuly($mysqli, $partner_email, $mentesi['partner'][$i], $gyakidd);
                    $sorozat_partner = getSorIsmTomb($mysqli, $gyakidd, $mentesi['partner'][$i]);
                    if(!isset($osszsuly['partner']['hiba'])) {
                        $diagram_adat["result_partner"][] = array(
                            "mentesidatum" => $mentesi['partner'][$i],
                            "osszsuly" => $osszsuly['partner']["osszsuly"],
                            "sorozat" => $sorozat_partner
                        );
                    }  
                }
            } else {
                $diagram_adat["partner_hiba"] = $mentesi['partner']["hiba"];
            }   
            
        }
            
        if(!isset($mentesi['sajat']["hiba"])) {
            for($i=0; $i<count($mentesi['sajat']); $i++) {
                $osszsuly['sajat'] = getOsszsuly($mysqli, $felhasznalo, $mentesi['sajat'][$i], $gyakidd);
                $sorozat_sajat = getSorIsmTomb($mysqli, $gyakidd, $mentesi['sajat'][$i]);
                
                if(!isset($osszsuly["sajat"]["hiba"])) {
                    //végeredményben ezt az adatot kell 
                    //elküldenem a edzésnapló napiterv oldának
                    $diagram_adat["result_sajat"][] = array(
                        "mentesidatum" => $mentesi['sajat'][$i],
                        "osszsuly" => $osszsuly['sajat']["osszsuly"],
                        "sorozat" => $sorozat_sajat
                    );
                }
            }
        } else {
            $diagram_adat["hiba"] = $mentesi['sajat']["hiba"];
        }   
        
        //tooltipnek szükség van az utolsóként lementett - elvégzett - gyakorlat sorozatára
        //szóval amugy is léteznie kell a megnevezes változónak, mert az alapján készítem el ezt az 
        // adatot is
        if(isset($_POST["gttp"]) && $_POST["gttp"] == "k") {
            if(!isset($result["hiba"]) && !isset($mentesi["hiba"])) {
                $diagram_adat["sorozat"] = getSorIsmTomb($mysqli, $result["gyak_id"], $mentesi["mentesi"]);
            }
        } 
    } else {
        $diagram_adat["hiba"] = "Nincs megfelelő adat a lekéréshez";
    }

    print json_encode($diagram_adat);
    exit;

    function getPartnerEmail($mysqli, $partnerid) {
        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        if($partnerid == 0 || $partnerid == null || $partnerid == "") {
            return "";
        }

        $sql = "SELECT email FROM felhasznalo WHERE azonosito = '{$partnerid}'";
        if($e = $mysqli->query($sql)) {
            if($mysqli->affected_rows != 0) {
                $res = $e->fetch_assoc();
                return $res['email'];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    //legkésöbb rögzített napló mentési dátuma
    function getMentesiDatum($mysqli, $felhasznalo, $gyakid) {
        global $hibaszoveg;
        $res = array();
        /*if($partner != null || $partner != "") {
            $sql_naplomentesi = "SELECT mentesidatum FROM naplo WHERE felhasznalo IN ('{$felhasznalo}','{$partner}')".
                            "AND gyak_id = '{$gyakid}' GROUP BY mentesidatum";
        } else {*/
            $sql_naplomentesi = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}'".
                            "AND gyak_id = '{$gyakid}' GROUP BY mentesidatum ORDER BY mentesidatum DESC";
        //}
        //legutolsó napló mentési dátuma, ami tartalmazza az adott gyakorlatot, csak a legelső mentési dátum kell
        /*$sql_naplomentesi = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}'".
                            "AND gyak_id = '{$gyakid}' GROUP BY mentesidatum"; //limit 1 volt*/
        if(!($eredmeny = $mysqli->query($sql_naplomentesi))) {
            $res["hiba"] = "Probléma a lekérdezésben: ".$mysqli->error;
        } else {
            if($eredmeny->num_rows > 0) {
                while($e = $eredmeny->fetch_assoc()) {
                    $res[] = $e["mentesidatum"];
                }
                
            } else {
                $res["hiba"] = $hibaszoveg;
            }
        }

        return $res;
    }

    //legkésöbb rögzített adatból kiszámított össz megmozgatott suly
    function getOsszsuly($mysqli, $felhasznalo, $mentesi, $gyakid) {
        $res = array();
        $adat = 0;

        /*if($partner != null || $partner != "") {
            $sql = "SELECT suly, ism FROM sorozat WHERE felhasznalo IN ('{$felhasznalo}','{$partner}') AND ".
                "mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakid}'";
        } else {*/
            $sql = "SELECT suly, ism FROM sorozat WHERE felhasznalo = '{$felhasznalo}' AND ".
                "mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakid}'";
        //}

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
        $sql_sorozat = "SELECT suly,ism, ismidopont FROM sorozat WHERE mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakorlat_id}'";
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

    function getGyakid($mysqli, $gyaknev) {
        $res = array();
        $tiszta_gyaknev = $mysqli->real_escape_string($gyaknev);
        $sql = "SELECT gyak_id FROM gyakorlat WHERE megnevezes = '{$tiszta_gyaknev}'";
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
?>