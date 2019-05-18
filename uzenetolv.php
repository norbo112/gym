<?php
    require_once("adbcuccok.inc.php");

    $kapcsolo = false;
    $torol = false;
    $uzenet = array();

    if(!isset($_COOKIE['felhasznalo'])) {
        $uzenet["hiba"] = "Be kell jelentkezned!";
        print json_encode($uzenet);
        exit;
    }

    if(isset($_POST["kapcsoloval"]) && $_POST["kapcsoloval"] != "") {
        //ha ez létezik akkor csak az olvasatlan üzenetek száma a lényeges
        $kapcsolo = true;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        //echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        $uzenet["hiba"] = "Kapcsolodási hiba: ".$kapcs->connect_error;
        print json_encode($uzenet);
        exit;
    }
    
    if(isset($_POST["torol"]) && $_POST["torol"] != "") {
        $torol = true;
        $torlendoid = $kapcs->real_escape_string($_POST["torol"]);
    }
    
    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']); 
    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

    if(!checkUser($biztFelh)) {
        error_log("Ismeretlen felhasználó: ".$_COOKIE['felhasznalo']);
        $uzenet["hiba"] = "Sajnos az adatok elérésekor hiba lépett fel";
        print json_encode($uzenet);
        exit;
    }


    //lekérem az olvasatlan üzeneteket
    if($kapcsolo && !$torol) {
        //ha van kapcsolo akkor csak az üzenetek mennyisége a lényeges
        $sql = "SELECT felhkitol, letrehozas, msgtype, msgcim, uzenet FROM usermsg WHERE olvasva = 0 AND felhki = '{$biztFelh}'";
    } else if(!$kapcsolo && !$torol) {
        //ha nem létezik kapcsolo, akkor mindegyik üzenetet olvasom (és elkészitem az obj-momat)
        //ezzel a lendülettel állítani is kellene az olvasott jelzőn
        $sql = "SELECT id,felhkitol, letrehozas, msgtype, msgcim, uzenet FROM usermsg WHERE felhki = '{$biztFelh}' ORDER BY letrehozas DESC";
    } else if($torol) {
        $sql = "DELETE FROM usermsg WHERE id = '{$torlendoid}'";
    }
    
    if($eredmeny = $kapcs->query($sql)) {
        if($kapcs->affected_rows == 0) {
            if($kapcsolo && !$torol) {
               $uzenet["uziszam"] = $kapcs->affected_rows; 
            } else {
               $uzenet["uziszam"] = $kapcs->affected_rows; 
               $uzenet["uzinote"] = "Nincsenek kapott üzeneteid";
            }
            
        } else {
            if($kapcsolo && !$torol) {
                $uzenet["uziszam"] = $kapcs->affected_rows;
            } else if(!$kapcsolo && !$torol) {
                //itt fogom felépíteni az obj-mat az üzenetekkel
                //
                $uzenet["uziszam"] = 0;
                
                while($tomb = $eredmeny->fetch_assoc()) {
                    $uzenet["uzik"][] = array(
                        "uid" => $tomb["id"],
                        "kitol" => $tomb["felhkitol"],
                        "mikor" => $tomb["letrehozas"],
                        "uzitipus" => $tomb["msgtype"],
                        "uzicim" => $tomb["msgcim"],
                        "uzibody" => $tomb["uzenet"]
                    );
                }  

                //miután sikerült átadnom beállítom az olvasott adatot pozitivra
                $sql = "UPDATE usermsg SET olvasva = 1 WHERE olvasva = 0 AND felhki = '{$biztFelh}'";
                if(!$kapcs->query($sql)) {
                    error_log("Sikertelen usermsg olvasva állítása");
                } else {
                    $uzenet["olvaspot"] = $kapcs->affected_rows;
                }
            } else if($torol) {
                if($kapcs->affected_rows == 1) {
                    $uzenet["siker"] = "Üzenet sikeresen törölve";
                } else {
                    $uzenet["siker"] = "Hiba az üzenet törlése közben";
                }
            }
        }
    } else {
        $uzenet["hiba"] = "Hiba történt a lekérdezés közben";
    }

    $kapcs->close();
    print json_encode($uzenet);
?>