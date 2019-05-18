<?php
    require_once("adbcuccok.inc.php");
    require_once("ellenorzes.inc");
    if(!isset($_POST['volte'])) {
        alert("Nem az ürlapról jöttél!", true);
        exit;
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        alert("Be kell jelentkezned", true);
        exit;
    }

    if(!checkUser($_COOKIE['felhasznalo'])) {
        alert("Be kell jelentkezned", true);
        exit;
    }

    $hibak = array();

    //irányítószám, varos, megye a nem közelező mezők ellenörzése
    if(isset($_POST['iranyitoszam']) && $_POST['iranyitoszam'] != "") {
        if(!ervenyes_iranyitoszam($_POST['iranyitoszam'])) {
            $hibak[] = "A megadott irányítószám hibás";
        }
    }

    //megfelelő megyék lettek e választva
    if(isset($_POST['megye']) && $_POST['megye'] != "") {
        if(!ervenyes_megye($_POST['megye'])) {
            $hibak[] = "Kérjük válasszon egy érvényes megyét";
        }
    }

    if(isset($_POST['varos']) && $_POST['varos'] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['varos'])) {
            $hibak[] = "Csak betűk és számok szerepelhetnek";
        } else if(strlen($_POST['varos']) > 40) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['cim']) && $_POST['cim'] != "") {
        if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['cim'])) {
            $hibak[] = "Csak betűk és számok szerepelhetnek";
        } else if(strlen($_POST['cim']) > 40) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['sajatsuly']) && $_POST['sajatsuly'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['sajatsuly'])) {
            $hibak[] = "A saját suly csak számot tartalmazhat";
        } else if(strlen($_POST['sajatsuly']) > 3) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['mellcsucs']) && $_POST['mellcsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['mellcsucs'])) {
            $hibak[] = "A fekvenyomó csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['mellcsucs']) > 3) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['guggolocsucs']) && $_POST['guggolocsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['guggolocsucs'])) {
            $hibak[] = "A guggoló csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['guggolocsucs']) > 3) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['felhuzocsucs']) && $_POST['felhuzocsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['felhuzocsucs'])) {
            $hibak[] = "A felhúzó csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['felhuzocsucs']) > 3) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    if(isset($_POST['magassag']) && $_POST['magassag'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['magassag'])) {
            $hibak[] = "A magasság csak számot tartalmazhat";
        } else if(strlen($_POST['magassag']) > 3) {
            $hibak[] = "Túl hosszú szöveget adtál meg";
        }
    }

    //végső ellenőrzés és regisztráció
    if(count($hibak) > 0) {
        alert($hibak, true);
    } else {
        if(felhasznalotRegisztral($_POST)) {
            alert("Sikeres adatfrissítés", false);
        } else {
            alert("A fiók frissítlse sikertelen {$_COOKIE['felhasznalo']}", true);
        }
    }

    function felhasznalotRegisztral($felhasznaloiAdatok)
    {
        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("MySQL kapcsolódás sikertelen: ".$mysqli->connect_error);
            return false;
        }

        $email = $mysqli->real_escape_string($_COOKIE['felhasznalo']);
        //ellenőrizzuk hogy van e már ilyen felszanáló
        $felhasznalotKeres = "SELECT azonosito FROM felhasznalo WHERE email = '{$email}'";
        if($keresesiEredmeny = $mysqli->query($felhasznalotKeres)) {
            if($mysqli->affected_rows == 0) {
                alert("Nem létezik $email felhasználó",true);
                return false;
            } else {
                $sor = $keresesiEredmeny->fetch_assoc();
                $azonosito = $sor['azonosito'];
            }
        }

        if(isset($felhasznaloiAdatok['cim']) || $felhasznaloiAdatok['cim'] != "") {
            $cim = $mysqli->real_escape_string($felhasznaloiAdatok['cim']);
        } else {
            $cim = "Nincs";
        }

        if(isset($felhasznaloiAdatok['varos']) || $felhasznaloiAdatok['varos'] != "") {
            $varos = $mysqli->real_escape_string($felhasznaloiAdatok['varos']);
        } else {
            $varos = "Nincs";
        }

        if(isset($felhasznaloiAdatok['megye']) || $felhasznaloiAdatok['megye'] != "") {
            $megye = $mysqli->real_escape_string($felhasznaloiAdatok['megye']);
        } else {
            $megye = "Nincs";
        }

        if(isset($felhasznaloiAdatok['iranyitoszam']) || $felhasznaloiAdatok['iranyitoszam'] != "") {
            $iranyitoszam = $mysqli->real_escape_string($felhasznaloiAdatok['iranyitoszam']);
        } else {
            $iranyitoszam = "None";
        }

        if(isset($felhasznaloiAdatok['magassag']) || $felhasznaloiAdatok['magassag'] != "") {
            $magassag = $mysqli->real_escape_string($felhasznaloiAdatok['magassag']);
        } else {
            $magassag = 0;
        }

        if(isset($felhasznaloiAdatok['sajatsuly']) || $felhasznaloiAdatok['sajatsuly'] != "") {
            $suly = (int) $mysqli->real_escape_string($felhasznaloiAdatok['sajatsuly']);
        } else {
            $suly = 0;
        }

        if(isset($felhasznaloiAdatok['mellcsucs']) || $felhasznaloiAdatok['mellcsucs'] != "") {
            $maxfek = (int) $mysqli->real_escape_string($felhasznaloiAdatok['mellcsucs']);
        } else {
            $maxfek = 0;
        }

        if(isset($felhasznaloiAdatok['guggolocsucs']) || $felhasznaloiAdatok['guggolocsucs'] != "") {
            $maxgugg = (int) $mysqli->real_escape_string($felhasznaloiAdatok['guggolocsucs']);
        } else {
            $maxgugg = 0;
        }

        if(isset($felhasznaloiAdatok['felhuzocsucs']) || $felhasznaloiAdatok['felhuzocsucs'] != "") {
            $felhuzocsucs = (int) $mysqli->real_escape_string($felhasznaloiAdatok['felhuzocsucs']);
        } else {
            $felhuzocsucs = 0;
        }

        if(isset($felhasznaloiAdatok['megoszt']) || $felhasznaloiAdatok['megoszt'] != "") {
            $megoszt = (int) $mysqli->real_escape_string($felhasznaloiAdatok['megoszt']);
        } else {
            $megoszt = 0;
        }

        //ez szükséges ha akarok ékezetes magyar betűket használni
        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        $lekerdezes = "UPDATE felhasznalo SET ".
            "megye='{$megye}',varos='{$varos}',cim='{$cim}',iranyitoszam='{$iranyitoszam}',".
            "suly='{$suly}',maxfek='{$maxfek}',maxgugg='{$maxgugg}',maxfelhuz='{$felhuzocsucs}',".
            "magassag='{$magassag}', megoszt='{$megoszt}' WHERE azonosito = '{$azonosito}'";
        
        if($mysqli->query($lekerdezes)) {
            $azon = $mysqli->insert_id;
            alert("{$email} fiók frissítve", false);
            return true;
        } else {
            error_log("Sikertelen adatf:::".$mysqli->connect_error);
            alert("Sikertelen adatfrissítés", true);
            return false;
        }
    } //frissítő függvény vége

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