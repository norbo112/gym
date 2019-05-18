<?php
    require_once("adbcuccok.inc.php");

    define("JOG", 1);

    if(!isset($_SESSION)) {
        session_start();
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

    //ellenőrzöm hogy létezik e a felhasználó, és admin e
$sql = "SELECT admin FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        echo "Nem létezik ilyen felhasználó ".$kapcs->error;
    } else {
        $sor = $eredmeny->fetch_assoc();
        $admine = $sor['admin'];
        $kimenet = "";
        if($admine == JOG) {
            //van e joga ellenőrizve, igy beküldheti a hirt
            //itt ellenőrzöm a bemenetet is
            if(isset($_POST['hir_cim']) || $_POST['hir_cim'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .]+$/u', $_POST['hir_cim'])) {
                    alert("Csak betüket és számokat tartalmazhat a cím");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $cim = $kapcs->real_escape_string($_POST['hir_cim']);
                }
            } else {
                $cim = "Üdvözlet az oldalról";
            }

            if(isset($_POST['hir_note']) || $_POST['hir_note'] != "") {
                if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .,\!\?\-\%]+$/um', $_POST['hir_note'])) {
                    alert("Csak betüket és számokat tartalmazhat a leírás");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $note = $kapcs->real_escape_string($_POST['hir_note']);
                }
            } else {
                $note = "Ismeretlen adat";
            }

            if(isset($_POST['hir_tipus']) || $_POST['hir_tipus'] != "") {
                $tipusok = array("Webalkalmazás","Fejlesztés","Eszközök","Általános");
                if(!in_array($_POST['hir_tipus'],$tipusok)) {
                    alert("Csak a kiválasztható típusok megengedettek");
                    echo file_get_contents("hirkuldo_form.html");
                    exit;
                } else {
                    $tipus = $kapcs->real_escape_string($_POST['hir_tipus']);
                }
            } else {
                $tipus = "Ismeretlen adat";
            }

            $sql = "INSERT INTO webapphirek (felhasznalo, letrehozas, hirtipus, hircim, hirtartalom)".
                " VALUES( '{$biztFelh}',NOW(), '{$tipus}','{$cim}','{$note}')";

                if($eredmeny = $kapcs->query($sql) ) {
                    if($kapcs->affected_rows > 0) {
                        echo "<h1>Hír beküldése</h1>";
                        alert("Sikeres hír felvétel");
                        echo file_get_contents("hirkuldo_form.html");
                    } else {
                        alert("Sikertelen hír felvétel");
                        echo "<h2>".$kapcs->connect_error()."</h2>";
                        echo file_get_contents("hirkuldo_form.html");
                    }
                } else {
                    echo "<h1>Hiba történt</h1>";
                }
        } else {
            //itt nem biztos hogy kell bármi tartalmat kiküldeni
            echo "<h1>Nincs adminisztrátóri jogod</h1>";
        }
    }

    $kapcs->close();

    function alert($szoveg) {
        echo '<div class="alert alert-success alert-dismissible">';
        echo '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        echo '<strong>'.$szoveg.' !</strong>';
        echo '</div>';
    }
?>