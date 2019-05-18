<?php
    require_once("adbcuccok.inc.php");
    require_once("ellenorzes.inc");

    $hibak = array();

    if(!isset($_POST['volte'])) {
        $hibak[] = "Nem az ürlapról jöttél!";
        //die(header("Location: reg.html"));
    }

    
    $kotelezo = array("knev","vnev","email","jelszo1","jelszo2");

    foreach($kotelezo as $kotelezoMezo) {
        if(!isset($_POST[$kotelezoMezo]) || $_POST[$kotelezoMezo] == "") {
            $hibak[] = $kotelezoMezo." megadása kötelező.";
        }
    }

    //csak számokat és betűket tartalmazhat a név értéke
    if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['knev'])) {
        $hibak[] = "A keresztnévben csak betűk és számok szerepelhetnek";
    } else if(strlen($_POST['knev']) > 50) {
        $hibak[] = "Túl hosszú szöveget adtál meg";
    }

    if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['vnev'])) {
        $hibak[] = "A vezetéknévben csak betűk és számok szerepelhetnek";
    } else if(strlen($_POST['vnev']) > 50) {
        $hibak[] = "Túl hosszú szöveget adtál meg";
    }

    //email ellenörzése php függvénnyel
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $hibak[] = "Nem megfelelő email cím";
    }

    //jelszó egyezés ellenőrzése
    if($_POST['jelszo1'] != $_POST['jelszo2']) {
        $hibak[] = "A két jelszó nem egyezik";
    }

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

    if(isset($_POST['sajatsuly']) && $_POST['sajatsuly'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['sajatsuly'])) {
            $hibak[] = "A saját suly csak számot tartalmazhat";
        } else if(strlen($_POST['sajatsuly']) > 3 ) {
            $hibak[] = "A sajatsuly csak három karakter lehet";
        }
    }

    if(isset($_POST['mellcsucs']) && $_POST['mellcsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['mellcsucs'])) {
            $hibak[] = "A fekvenyomó csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['mellcsucs']) > 3 ) {
            $hibak[] = "A mellcsucs csak három karakter lehet";
        }
    }

    if(isset($_POST['guggolocsucs']) && $_POST['guggolocsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['guggolocsucs'])) {
            $hibak[] = "A guggoló csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['guggolocsucs']) > 3 ) {
            $hibak[] = "A guggolocsucs csak három karakter lehet";
        }
    }

    if(isset($_POST['felhuzocsucs']) && $_POST['felhuzocsucs'] != "") {
        if(!preg_match('/^[\d]+$/u',$_POST['felhuzocsucs'])) {
            $hibak[] = "A felhúzó csúcsod csak számot tartalmazhat";
        } else if(strlen($_POST['felhuzocsucs']) > 3 ) {
            $hibak[] = "A felhúzó csúcsod csak három karakter lehet";
        }
    }

    //ellenörzöm a megfelelő nem kiválasztását
    $nemek = array("Férfi", "Nő");
    if(isset($_POST['genre']) && $_POST['genre'] != "") {
        if(!in_array($_POST['genre'],$nemek)) {
        $hibak[] = "Férfi vagy Nő nemet adhatsz csak meg";
        }
    }
    

    sleep(5);
    //végső ellenőrzés és regisztráció
    if(count($hibak) > 0) {
        foreach($hibak as $h) {
            echo "$h <br>\n";
        }
    } else {
        if(felhasznalotRegisztral($_POST)) {
            echo "Sikeres regisztráció";
        } else {
            error_log("A felhasználó regisztrációja sikertelen: {$_POST['email']}");
            echo "A fiók regisztrációja sikertelen {$_POST['email']}";
        }
    }

    function felhasznalotRegisztral($felhasznaloiAdatok)
    {
        $mysqli = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($mysqli->connect_errno) {
            error_log("MySQL kapcsolódás sikertelen: ".$mysqli->connect_error);
            return false;
        }

        $email = $mysqli->real_escape_string($felhasznaloiAdatok['email']);
        //ellenőrizzuk hogy van e már ilyen felszanáló
        $felhasznalotKeres = "SELECT azononsito FROM felhasznalo WHERE email = '{$email}'";
        if($keresesiEredmeny = $mysqli->query($felhasznalotKeres)) {
            $sortKeres = $keresesiEredmeny->fetch_assoc();
        }
        
        
        if(isset($sortKeres['azononsito']) && $sortKeres['azononsito'] != "") {
            $hibak[] = "Ezzel az email címmel már létezik felhasználó";
            return false;
        }

        $vezetekNev = $mysqli->real_escape_string($felhasznaloiAdatok['vnev']);
        $keresztNev = $mysqli->real_escape_string($felhasznaloiAdatok['knev']);
        $options = [
            'cost' => 11,
            'salt' => random_bytes(22)
        ];
        $titkositottJelszo = password_hash($felhasznaloiAdatok['jelszo1'],PASSWORD_BCRYPT, $options);
        //$titkositottJelszo = @crypt($felhasznaloiAdatok['jelszo1']);
        $jelsz = $mysqli->real_escape_string($titkositottJelszo);

        if(isset($felhasznaloiAdatok['cim']) && $felhasznaloiAdatok['cim'] != "") {
            $cim = $mysqli->real_escape_string($felhasznaloiAdatok['cim']);
        } else {
            $cim = "Nincs";
        }

        if(isset($felhasznaloiAdatok['varos']) && $felhasznaloiAdatok['varos'] != "") {
            $varos = $mysqli->real_escape_string($felhasznaloiAdatok['varos']);
        } else {
            $varos = "Nincs";
        }

        if(isset($felhasznaloiAdatok['megye']) && $felhasznaloiAdatok['megye'] != "") {
            $megye = $mysqli->real_escape_string($felhasznaloiAdatok['megye']);
        } else {
            $megye = "Nincs";
        }

        if(isset($felhasznaloiAdatok['iranyitoszam']) && $felhasznaloiAdatok['iranyitoszam'] != "") {
            $iranyitoszam = $mysqli->real_escape_string($felhasznaloiAdatok['iranyitoszam']);
        } else {
            $iranyitoszam = "None";
        }

        if(isset($felhasznaloiAdatok['magassag']) && $felhasznaloiAdatok['magassag'] != "") {
            $magassag = $mysqli->real_escape_string($felhasznaloiAdatok['magassag']);
        } else {
            $magassag = 0;
        }

        if(isset($felhasznaloiAdatok['sajatsuly']) && $felhasznaloiAdatok['sajatsuly'] != "") {
            $suly = $mysqli->real_escape_string($felhasznaloiAdatok['sajatsuly']);
        } else {
            $suly = 0;
        }

        if(isset($felhasznaloiAdatok['mellcsucs']) && $felhasznaloiAdatok['mellcsucs'] != "") {
            $maxfek = $mysqli->real_escape_string($felhasznaloiAdatok['mellcsucs']);
        } else {
            $maxfek = 0;
        }

        if(isset($felhasznaloiAdatok['guggolocsucs']) && $felhasznaloiAdatok['guggolocsucs'] != "") {
            $maxgugg = $mysqli->real_escape_string($felhasznaloiAdatok['guggolocsucs']);
        } else {
            $maxgugg = 0;
        }

        if(isset($felhasznaloiAdatok['felhuzocsucs']) && $felhasznaloiAdatok['felhuzocsucs'] != "") {
            $felhuzocsucs = $mysqli->real_escape_string($felhasznaloiAdatok['felhuzocsucs']);
        } else {
            $felhuzocsucs = 0;
        }

        if(isset($felhasznaloiAdatok['genre']) && $felhasznaloiAdatok['genre'] != "") {
            $genre = $mysqli->real_escape_string($felhasznaloiAdatok['genre']);
        } else {
            $genre = "";
        }

        //összesítőhoz
        if(isset($felhasznaloiAdatok['megoszt']) && $felhasznaloiAdatok['megoszt'] != "") {
            //1 ha igen, 0 ha nem
            $megoszt = (int) $mysqli->real_escape_string($felhasznaloiAdatok['megoszt']);
        } else {
            $megoszt = 0;
        }

        //ez szükséges ha akarok ékezetes magyar betűket használni
        $mysqli->query("SET NAMES 'UTF8'");
        $mysqli->query("SET CHARACTER SET 'UTF8'");

        $lekerdezes = "INSERT INTO felhasznalo (vnev,knev,email,jelszo,megye,varos,cim,iranyitoszam,suly,maxfek,maxgugg,letrejott,maxfelhuz, magassag, genre, megoszt)".
                      " VALUES ('{$vezetekNev}','{$keresztNev}','{$email}','{$jelsz}','{$megye}','{$varos}','{$cim}','{$iranyitoszam}'".
                      ",'{$suly}','{$maxfek}','{$maxgugg}',NOW(),'{$felhuzocsucs}','{$magassag}','{$genre}', '{$megoszt}')";
        
        if($mysqli->query($lekerdezes)) {
            $azon = $mysqli->insert_id;
            error_log("{$email} fiók beszúrva {$azon} azonosítóval");
            return true;
        } else {
            error_log("{$lekerdezes} beszúrása sikertelen");
            return false;
        }
    } //regisztrációs függvény vége
?>