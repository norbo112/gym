<?php
    require_once("adbcuccok.inc.php");

    $resultok = array();
    $kapcsolo = 0;
    header("Content-Type: application/json");

    if(!isset($_SESSION)) {
        session_start(); 
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        $resultok[] = "Be kell jelentkezned!";
        print json_encode($resultok);
        exit;
    }

    if(!isset($_POST['uid']) || $_POST['uid'] == "") {
        $resultok[] = "Kérlek ad meg hogy melyik hírt szeretnéd kommentálni";
        print json_encode($resultok);
        exit;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        $resultok[] = "Kapcsolódási hiba";
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        print json_encode($resultok);
        exit;
    }
    
    if(!isset($_POST["kapcsolo"])) {
        if(isset($_POST['wbtext']) || $_POST['wbtext'] != "") {
            if(!preg_match('/^[\w\p{L}\p{N}\p{Pd} .,\?\!]+$/um', $_POST['wbtext'])) {
                $resultok["hiba"] = "Csak betüket és számokat tartalmazhat a leírás";
                print json_encode($resultok);
                exit;
            } else {
                $note = $kapcs->real_escape_string($_POST['wbtext']);
            }
        }
    } else {
        if($_POST["kapcsolo"] != "") {
            $kapcsolo = $_POST["kapcsolo"];
        }
    }

    

    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    if(!checkUser($biztFelh)) {
        error_log("Ismeretlen felhasználói string");
        $resultok[] = "Ismeretlen felhasználó hiba";
        print json_encode($resultok);
        exit;
    }

    $whid = $kapcs->real_escape_string($_POST['uid']);
    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    //ellenőrzöm hogy létezik e a felhasználó
    $sql = "SELECT email FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        $resultok[] = "Nem létezik ilyen felhasználó ";
        error_log("Nem létezik ilyen felhasználó".$kapcs->connect_error);
        print json_encode($resultok);
        exit;
    } else {
        //ha van kapcsoló akkor lekérdezem a webhírhez tartozó üziket
        //itt elküldöm az üzenetet
        if($kapcsolo == 1) {
            $sql = "SELECT * FROM webhirekhsz WHERE webhirid = '{$whid}' ORDER BY webhirdate DESC";
        } else if($kapcsolo == 2) {
            //ez lesz a törlés kérése
            if(!checkAdmine($_COOKIE['felhasznalo'])) {
                $resultok[] = "Hiba történt, kérlek próbáld meg újra";
                error_log("Nincs adminisztárori jogod a törléshez");
                print json_encode($resultok);
                exit;
            } else {
                $sql = "DELETE FROM webapphirek WHERE id = '{$whid}'";
                
            }
        } else {
            //nincs kapcsoló tehát hozzáadok
            $sql = "INSERT INTO webhirekhsz (webhirid,webhirdate,felhkitol,webhirtext)".
            " VALUES( '{$whid}',NOW(),'{$biztFelh}','{$note}')";
        }
        

        if($eredmeny = $kapcs->query($sql) ) {
            if($kapcs->affected_rows > 0) {
                if($kapcsolo == 1) {
                    while($tomb = $eredmeny->fetch_assoc()) {
                        $resultok[] = array(
                            "id" => $tomb["webhirid"],
                            "letrehozva" => $tomb["webhirdate"],
                            "kitol" => $tomb["felhkitol"],
                            "szoveg" => $tomb["webhirtext"]
                        );
                    }
                } else if($kapcsolo == 2) {
                    $sqlhsz = "DELETE FROM webhirekhsz WHERE webhirid = '{$whid}'";
                    if(hirekHszCheck($kapcs, $whid)) {
                        if($kapcs->query($sqlhsz)) {
                            $resultok[] = "Sikeresen eltávolítottad a hozzászólásokat is";
                        } else {
                            $resultok[] = "Sikertelen a hozzászólások törlése";
                        }
                    }
                    $resultok[] = "Sikeres törlés : ".$kapcs->affected_rows." sor";
                } else {
                    $resultok[] = "Sikeres hozzászólás küldés";
                }
                
            } else {
                if($kapcsolo == 1) {
                    $resultok["nulla"] = "Nincsenek hozzászólások";
                    print json_encode($resultok);
                    exit; 
                } else if($kapcsolo == 2) {
                    $resultok[] = "Sikertelen törlés";
                    error_log("Sikertelen törlés".$kapcs->error);
                    print json_encode($resultok);
                    exit; 
                } else {
                    $resultok["hiba"] = "Sikertelen hozzászólás küldés";
                    error_log("Sikertelen üzi felvétel".$kapcs->error);
                    print json_encode($resultok);
                    exit; 
                }                    
            }        
        } else {
            $resultok[] = "Lekérdezés hiba".$kapcs->error;
            error_log("Lekérdezés hiba: ".$kapcs->error);
        }
    }

    $kapcs->close();
    print json_encode($resultok);

    function hirekHszCheck($kapcs, $hirid) {
        $sqlhsz = "SELECT COUNT(id) FROM webhirekhsz WHERE webhirid = '{$hirid}'";
        if($eredmeny = $kapcs->query($sqlhsz)) {
            if($eredmeny->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
?>