<?php
    require_once("adbcuccok.inc.php");

    $izomcsoport = array();
    $select_elem = "";
    $kapcsolo = false;
    $felhasznalo = false;

    $sgyak = null; //az szerkesztésre váró obj-m
    $sgyakid = null;
    $nev = null;
    $csoport = null;
    $gyleiras = null;
    $gyvideoid = null;
    $gyvideopoz = null;
  
    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        echo "MySQL kapcsolódási hiba".$kapcs->connect_error;
        exit;
    }

    if(isset($_COOKIE["felhasznalo"]) && $_COOKIE["felhasznalo"] != "") {
        $felhasznalo = $kapcs->real_escape_string($_COOKIE["felhasznalo"]);
    }


    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

    /**
     * Ha küldök kapcsolót akkor lekérdezem a kiválasztott gyakorlatot hányan használták
     * az összes naplókon át
     */
    if(isset($_POST['kapcsolo']) && $_POST['kapcsolo'] != "") {
        if(isset($_POST['gyaksi']) && $_POST['gyaksi'] != "") {
            $kapcsolo = $_POST['kapcsolo'];
            $sgyak = json_decode($_POST['gyaksi']);
        } else if(isset($_POST['gyakid']) && $_POST['gyakid'] != "") {
            $kapcsolo = $_POST['kapcsolo'];
            $gyakid = $kapcs->real_escape_string($_POST['gyakid']);
        } else {
            $kapcsolo = false;
            $gyakid = 0;
        }    
    }

    if($kapcsolo == 1) {
        if($felhasznalo) {
            $sql = "SELECT COUNT(naplo.gyak_id) as hasznalat, gyakorlat.leiras as leiras FROM naplo, gyakorlat WHERE ".
                   "naplo.gyak_id = '{$gyakid}' AND felhasznalo = '{$felhasznalo}'".
                   "AND gyakorlat.gyak_id = '{$gyakid}'";
        } else {
            $sql = "SELECT COUNT(naplo.gyak_id) as hasznalat, gyakorlat.leiras as leiras FROM naplo, gyakorlat WHERE naplo.gyak_id = '{$gyakid}' AND gyakorlat.gyak_id = '{$gyakid}'";
        }
        

        if($eredmeny = $kapcs->query($sql)) {
            $adatok = array();
            $sor = $eredmeny->fetch_assoc();
            $leiras = $sor['leiras'];
            $adatok['leiras'] = $leiras;
            $adatok['hasznalatnum'] = $sor['hasznalat'];
            header("Content-Type: application/json");
            print json_encode($adatok);
            exit;
        } else {
            header("Content-Type: application/json");
            $adatok["hiba"] = "Hiba a gyakorlat információk lekérdezésében".$kapcs->error;
            error_log("Hiba a gyakorlat információk lekérdezésében".$kapcs->error);
            print json_encode($adatok);
            exit;
        }
    } else if($kapcsolo == 2 && checkAdmine($_COOKIE['felhasznalo'])) {
        //lekérem az adatokat, majd jsonba visszaküldöm az oldalnak szerkesztésre
        $szerkgyakadat = array();
        $sqlgyak = "SELECT * FROM gyakorlat WHERE gyak_id = '{$gyakid}'";
        if($eredmeny = $kapcs->query($sqlgyak)) {
            if($eredmeny->num_rows == 0) {
                $szerkgyakadat["hiba"] = "Nem létezik a gyakorlat ".$kapcs->error;
            } else {
                $sor = $eredmeny->fetch_assoc();
                $szerkgyakadat["gyakmodal"] = GyakModal($sor);
            }

            header("Content-Type: application/json");
            print json_encode($szerkgyakadat);
            exit;
        } else {
            $szerkgyakadat["hiba"] = "Kérlek próbáld meg késöbb";
            error_log("Hiba a gyakorlat info lekérése során: ".$kapcs->error);
            header("Content-Type: application/json");
            print json_encode($szerkgyakadat);
            exit;
        }
    } else if($kapcsolo == 3 && checkAdmine($_COOKIE['felhasznalo'])) {
        if(szerkGyakCheck($kapcs, $sgyak)) {
            $sqlgyakszerk = "UPDATE gyakorlat SET csoport = '{$csoport}', megnevezes = '{$nev}',".
                "leiras = '{$gyleiras}', videolink = '{$gyvideoid}', videostartpoz = '{$gyvideopoz}'".
                "WHERE gyak_id = '{$sgyakid}'";
            if($kapcs->query($sqlgyakszerk)) {
                header("Content-Type: application/json");
                $adatok2["success"] = "Sikeres adat frissítés";
                print json_encode($adatok2);
                exit;
            } else {
                header("Content-Type: application/json");
                $adatok2["hiba"] = "Hiba a gyakorlat info frissítése során: ".$kapcs->error;
                error_log("Hiba a gyakorlat info frissítése során: ".$kapcs->error);
                print json_encode($adatok2);
                exit;
            }
            
        } else {
            header("Content-Type: application/json");
            $adatok["hiba"] = "Hiba a gyakorlat információ feldolgozása során";
            error_log("Helytelen adatok a gyakorlat szerkesztésekor");
            print json_encode($adatok);
            exit;
        }
        
    }

    //elöször lekérdezem az izomcsoportokat, majd ezeket optgroup elemekbe helyezem...
    $sql = "SELECT csoport FROM gyakorlat GROUP BY csoport";
    $eredmeny = $kapcs->query($sql);
    if(!$eredmeny) {
        echo "Hiba a lekérdezésben";
        exit;
    }

    while($sor = $eredmeny->fetch_assoc()) {
        foreach($sor as $ertek) {
            $izomcsoport[] = $ertek;
        }
    }

    for($i = 0; $i<count($izomcsoport); $i++) {
        $sql = "SELECT gyak_id, megnevezes, videolink, videostartpoz FROM gyakorlat WHERE csoport = '{$izomcsoport[$i]}'";
        if($eredmeny = $kapcs->query($sql)) {
            $select_elem .= "<h4 class='listahead'>{$izomcsoport[$i]}</h4>\n";
            while($sor = $eredmeny->fetch_assoc()) {
                $vanevid = ($sor['videolink'] != "")? ' vanlink' : "";
                $select_elem .= "<li class='list-group-item'><a class='".$vanevid."' href='javascript:void(0)' onclick='showvideo(\"{$sor['videolink']}\",\"{$sor['megnevezes']}\",\"{$sor['gyak_id']}\",\"{$sor['videostartpoz']}\")'>{$sor['megnevezes']}</a>".adminfunk($sor['gyak_id'])."\n</li>";
            }
        } else {
            echo "Hiba az adatbázis elérésében";
        }
    }

    echo '<div class="list-group lgt2">';
    echo $select_elem;
    echo '</div>';
    $kapcs->close();

    function adminfunk($gyakid) {
        if(@checkAdmine($_COOKIE['felhasznalo'])) {
            return "<a style='display:flex;' class='btn btn-info pull-right' onclick='gyakszerk(\"$gyakid\")'><span class='glyphicon glyphicon-edit'></span></a>";
        } else {
            return "";
        }
    }

    function GyakModal($adat) {
        $str = "";
        //$str .= "<button type='button' class='btn btn-default btn-block' data-toggle='modal' data-target='#myModal'>Új gyakorlat</button>\n";
        $str .= '<div id="myGyakModal" class="modal fade" role="dialog" style="padding-top: 10px">';
        $str .= '<div class="modal-dialog">';
        $str .= '<div class="modal-content">';
        $str .= '<div class="modal-header">';
        $str .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $str .= '<h4 class="modal-title">'.$adat['megnevezes'].' gyakorlat szerkesztése</h4>';
        $str .= '</div>';
        $str .= '<div class="modal-body">';
        $str .= '<form id="ujgyakszerk" action="#" method="post">';
        $str .= '<div id="hibaDiv"></div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_nev">Gyakorlat neve: </label>';
        $str .= '<input class="form-control" type="text" name="ujgyak_nev" id="ujgyak_nev" value="'.$adat['megnevezes'].'"/>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_nevHiba">';
        $str .= '    Gyakorlat nevének megadása kötelező! csak betüket és számokat tartalmazhat';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_tipus">Gyakorlat Típusa: </label>';
        $str .= '<input class="form-control" type="text" name="ujgyak_tipus" id="ujgyak_tipus" value="'.$adat['csoport'].'"/>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_tipusHiba">';
        $str .= '    Pl Mell, Hát, - Váll megadása kötelező!';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_leiras">Leírás: </label>';
        $str .= '<textarea class="form-control" name="ujgyak_leiras" id="ujgyak_leiras" cols="40" rows="7">"'.$adat['leiras'].'"</textarea>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_leirasHiba">';
        $str .= '    Ha kitöltöd, speciális karaktereket nem tartalmazhat!';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_video">Video Link/ID: </label>';
        $str .= '<input type="text" class="form-control" name="ujgyak_video" id="ujgyak_video" value="'.$adat['videolink'].'"/>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_videoHiba">';
        $str .= '        Ha kitöltöd, speciális karaktereket nem tartalmazhat!';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="poz_video">Video Set Poz: </label>';
        $str .= '<input type="text" class="form-control" name="poz_video" id="poz_video" value="'.$adat['videostartpoz'].'"/>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="poz_videoHiba">';
        $str .= '        Csak maximum, 3 db számot adhatsz meg!';
        $str .= '</span>';
        $str .= '<input type="hidden" name="gyakid" id="gyakid" value="'.$adat['gyak_id'].'"/>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<button class="btn btn-default" type="submit">Mentés</button>';
        $str .= '<button class="btn btn-default" type="button" onclick="resetvan()">Reset</button>';
        $str .= '</div>';
        $str .= '</form>';
        $str .= '</div>';
        $str .= '<div class="modal-footer">';
        $str .= '    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetvan()">Bezár</button>';
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        return $str;
    }

    function szerkGyakCheck($kapcs, $gyakobj) {
        //itt késöbb muszály lesz ellenőrzéseket végeznem
        global $sgyakid, $nev,$csoport,$gyleiras,$gyvideoid,$gyvideopoz;
        $sgyakid = $kapcs->real_escape_string($gyakobj->gyakid);
        $nev = $kapcs->real_escape_string($gyakobj->nev);
        $csoport = $kapcs->real_escape_string($gyakobj->csoport);
        $gyleiras = $kapcs->real_escape_string($gyakobj->leiras);
        $gyvideoid = $kapcs->real_escape_string($gyakobj->videoid);
        $gyvideopoz = $kapcs->real_escape_string($gyakobj->videopoz);
        
        return true;
    }
?>