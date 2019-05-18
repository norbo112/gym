<?php
    require_once("adbcuccok.inc.php");
    define("JOG", 1);

    $adminok = array();

    /**
     * Létrekell hozzak a sessionba egy idő változot, amikor is ellett küldve az üzenet
     * nem akarom engedni hogy esetleg spammaljék, igy pl 15-20 percenként lehet 
     * üzenetet küldeni egy felhasználónak , ezt majd késöbb fogom megoldani
     */

    if(!isset($_SESSION)) {
        session_start(); 
    }

    if(!isset($_SESSION['uzitime'])) {
        $_SESSION['uzitime'] = time();
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        alert("Be kell jelentkezned!", true);
        exit;
    }

    if(!isset($_POST['kinek']) || $_POST['kinek'] == "") {
        alert("Kérlek ad meg hogy kinek szeretnéd küldeni az üzit",true);
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        alert("Kapcsolódási hiba", true);
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        exit;
    }

    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    $biztKinek = $kapcs->real_escape_string($_POST['kinek']);
    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    //ellenőrzöm hogy létezik e a felhasználó
$sql = "SELECT email FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        alert("Nem létezik ilyen felhasználó ", true);
        error_log("Nem létezik ilyen felhasználó".$kapcs->connect_error);
    } else {
        $sor = $eredmeny->fetch_assoc();
        $kimenet = "";
        //nem kell admin ellenőrzés
        //itt ellenőrzöm a bemenetet is
            if(isset($_POST['uzi_cim']) || $_POST['uzi_cim'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['uzi_cim'])) {
                    alert("Csak betüket és számokat tartalmazhat a cím",true);
                    exit;
                } else if(strlen($_POST['uzi_cim']) > 300) {
                    alert("Től sokat szeretnél!", true);
                    exit;
                } else {
                    $cim = $kapcs->real_escape_string($_POST['uzi_cim']);
                }
            } else {
                $cim = "Üdvözlet az oldalról";
            }

            if(isset($_POST['uzi_note']) || $_POST['uzi_note'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .,\?\!]+$/um', $_POST['uzi_note'])) {
                    alert("Csak betüket és számokat tartalmazhat a leírás", true);
                    exit;
                } else if(strlen($_POST['uzi_note']) > 300) {
                    alert("Től sokat szeretnél!", true);
                    exit;
                } else {
                    $note = $kapcs->real_escape_string($_POST['uzi_note']);
                }
            } else {
                $note = "Ismeretlen adat";
            }

            //itt elküldöm az üzenetet
            $sql = "INSERT INTO usermsg (felhki, felhkitol, letrehozas, msgcim, uzenet)".
                " VALUES( '{$biztKinek}','{$biztFelh}',NOW(),'{$cim}','{$note}')";

            if($eredmeny = $kapcs->query($sql) ) {
                if($kapcs->affected_rows > 0) {
                    alert("Sikeres üzenet küldés", false);
                } else {
                    alert("Sikertelen üzenet küldés",true);
                    error_log("Sikertelen üzi felvétel".$kapcs->connect_error());                  
                }
                    
            } else {
                    alert("Hibák történtek",true);
            }
    }

    $kapcs->close();

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