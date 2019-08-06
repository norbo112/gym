<?php
    //define("ADBUSER","edzesnap_phpuser"); //weben lávő user
    define("ADBUSER","phpuser"); 
    define("ADBPASS","kalitka15N");
    define("ADBSERVER","localhost");
    //define("ADBDB","edzesnap_edzesnaplo");
    define("ADBDB","edzesnaplo");

    function checkUser($felhasznalo) {
        $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($kapcs->connect_errno) {
            //echo "kapcs hiba: ".$kapcs->connect_error;
            error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
            return false;
        } 

        if(!isset($felhasznalo) || $felhasznalo == "") {
            error_log("Be kell jelentkezned");
            return false;
        } else {
            if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} \_\-.@]+$/u', $felhasznalo)) {
                error_log("Hibás:: $felhasznalo ::string");
                return false;
            }
        }

        $biztFelh = $kapcs->real_escape_string($felhasznalo);

        mysqli_query($kapcs,"SET NAMES 'UTF8'");
        mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

        //ellenőrzöm hogy létezik e a felhasználó
        $sql = "SELECT email FROM felhasznalo WHERE email = '{$biztFelh}'";
        if(!$eredmeny = $kapcs->query($sql)) {
            error_log("Nem létezik ilyen felhasználó".$kapcs->connect_error); 
            return false;
        }

        if($eredmeny->num_rows == 0) {
            return false;
        }

        $kapcs->close();
        return true;
    }

    function checkAdmine($felhasznalo) {
        $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($kapcs->connect_errno) {
            //echo "kapcs hiba: ".$kapcs->connect_error;
            error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
            $kapcs->close();
            return false;
        } 

        if(!isset($felhasznalo) || $felhasznalo == "") {
            error_log("Be kell jelentkezned");
            return false;
        } else {
            if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} \_\-.@]+$/u', $felhasznalo)) {
                error_log("Hibás:: $felhasznalo ::string");
                $kapcs->close();
                return false;
            }
        }

        $biztFelh = $kapcs->real_escape_string($felhasznalo);

        mysqli_query($kapcs,"SET NAMES 'UTF8'");
        mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

        //ellenőrzöm hogy létezik e a felhasználó
        $sql = "SELECT admin FROM felhasznalo WHERE email = '{$biztFelh}'";
        if(!$eredmeny = $kapcs->query($sql)) {
            error_log("Nem létezik ilyen felhasználó".$kapcs->connect_error);
            $kapcs->close();
            return false;
        }

        if($eredmeny->num_rows == 0) {
            $kapcs->close();
            return false;
        } else {
            $sor = $eredmeny->fetch_assoc();
            if($sor['admin'] == 1) {
                $kapcs->close();
                return true;
            } else {
                $kapcs->close();
                return false;
            }
        }
    }

    function getUserID($felhasznalo) {
        $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($kapcs->connect_errno) {
            //echo "kapcs hiba: ".$kapcs->connect_error;
            error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
            return false;
        } 

        if(!isset($felhasznalo) || $felhasznalo == "") {
            error_log("Be kell jelentkezned");
            return false;
        } else {
            if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} \_\-.@]+$/u', $felhasznalo)) {
                error_log("Hibás:: $felhasznalo ::string");
                return false;
            }
        }

        $biztFelh = $kapcs->real_escape_string($felhasznalo);

        mysqli_query($kapcs,"SET NAMES 'UTF8'");
        mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

        //ellenőrzöm hogy létezik e a felhasználó
        $sql = "SELECT azonosito FROM felhasznalo WHERE email = '{$biztFelh}'";
        if(!$eredmeny = $kapcs->query($sql)) {
            error_log("Nem létezik ilyen felhasználó".$kapcs->connect_error); 
            return false;
        }

        if($eredmeny->num_rows == 0) {
            return false;
        } else {
            $e = $eredmeny->fetch_assoc();
            $id = $e["azonosito"];
        }

        $kapcs->close();
        return $id;
    }
?>