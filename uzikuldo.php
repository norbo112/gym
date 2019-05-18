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

    if(!isset($_COOKIE['felhasznalo'])) {
        echo "Be kell jelentkezned!";
        exit;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        exit;
    }

    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    //adminoknak küldöm az üzenetet(a vendégkönyvhöz hasonlóan)
    //elöször kikell válasszam azokat a felhasználókat akik adminok, majd ezeknek
    //is külön külön ciklusban elküldöm az üzenetet
    $sql = "SELECT email FROM felhasznalo WHERE admin = 1";
    if(!$eredmeny = $kapcs->query($sql)) {
        error_log("Nincsenek admin felhasználók".$kapcs->connect_error);
        echo "Hiba lépett fel!";
    } else {
        while($sor = $eredmeny->fetch_assoc()) {
            $adminok[] = $sor["email"];
        }
    }

    //ellenőrzöm hogy létezik e a felhasználó, és admin e
$sql = "SELECT email, admin FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        echo "Nem létezik ilyen felhasználó ".$kapcs->connect_error;
    } else {
        $sor = $eredmeny->fetch_assoc();
        $admine = $sor['admin'];
        $kimenet = "";
        //nem kell admin ellenőrzés
        //itt ellenőrzöm a bemenetet is
            if(isset($_POST['uzi_cim']) || $_POST['uzi_cim'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['uzi_cim'])) {
                    alert("Csak betüket és számokat tartalmazhat a cím");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $cim = $kapcs->real_escape_string($_POST['uzi_cim']);
                }
            } else {
                $cim = "Üdvözlet az oldalról";
            }

            if(isset($_POST['uzi_note']) || $_POST['uzi_note'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} \.\,\?\!]+$/um', $_POST['uzi_note'])) {
                    alert("Csak betüket és számokat tartalmazhat a leírás");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $note = $kapcs->real_escape_string($_POST['uzi_note']);
                }
            } else {
                $note = "Ismeretlen adat";
            }

            if(isset($_POST['uzi_tipus']) || $_POST['uzi_tipus'] != "") {
                $tipusok = array("Webalkalmazás","Fejlesztés","Eszközök","Általános");
                if(!in_array($_POST['uzi_tipus'],$tipusok)) {
                    alert("Csak a kiválasztható típusok megengedettek");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $tipus = $kapcs->real_escape_string($_POST['uzi_tipus']);
                }
            } else {
                $tipus = "Ismeretlen adat";
            }

            //itt minden admin felhasználónak elküldöm az üzenetet
            foreach($adminok as $a) {
                $sql = "INSERT INTO usermsg (felhki, felhkitol, letrehozas, msgtype, msgcim, uzenet)".
                    " VALUES( '{$a}','{$biztFelh}',NOW(), '{$tipus}','{$cim}','{$note}')";

                if($eredmeny = $kapcs->query($sql) ) {
                    if($kapcs->affected_rows > 0) {
                        alert("Sikeres hír felvétel");
                    } else {
                        alert("Sikertelen hír felvétel");
                        error_log("Sikertelen üzi felvétel".$kapcs->connect_error());                  
                    }

                    
                } else {
                    echo "<h1>Hiba történt</h1>";
                }
            }

            echo "<h1>Üzenet küldése az adminisztátoroknak</h1>";
            echo file_get_contents("uzenetkuldo.html");
    }

    $kapcs->close();

    function alert($szoveg) {
        echo '<div class="alert alert-success alert-dismissible">';
        echo '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        echo '<strong>'.$szoveg.' !</strong>';
        echo '</div>';
    }
?>