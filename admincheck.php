<?php
    require_once("adbcuccok.inc.php");

    define("JOG", 1);
    $csakadmine = false;
    $delgomb = false;
    $useremail = false;

    if(!isset($_SESSION)) {
        session_start();
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        error_log("Illetéktelen hozzáférés az admincheck oldalhoz\n");
        echo "";
        exit;
    } else if(!checkUser($_COOKIE['felhasznalo'])) {
        error_log("Illetéktelen felhasználó azonosító");
        echo "";
        exit;
    }

    if(isset($_POST['csaka']) && $_POST['csaka']!="") {
        $csakadmine = true;
    }

    /*if(isset($_POST['delg']) && $_POST['delg']!="") {
        $delgomb = true;
        if(isset($_POST['delus']) && !empty($_POST['delus'])) {
            $useremail = trim($_POST['delus']);
        }
    }*/

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        echo "";
        exit;
    }

    $biztFelh = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    $kapcs->query("SET NAMES UTF8");
    $kapcs->query("SET CHARACTER SET UTF8");

    //ellenőrzöm hogy létezik e a felhasználó, és admin e
$sql = "SELECT admin FROM felhasznalo WHERE email = '{$biztFelh}'";
    if(!$eredmeny = $kapcs->query($sql)) {
        error_log("Nem létezik ilyen felhasználó ".$kapcs->connect_error);
        echo "";
    } else {
        $sor = $eredmeny->fetch_assoc();
        $admine = $sor['admin'];
        $kimenet = "";
        if($admine == JOG) {
            //van joga, igy kiirom a gombot amit használhat az admin felhasználó a 
            // gyakorlat hozzáadására, a modal-t is hozzá kell adnom innen, mivel
            //ha az oldalon van a kod, akkor az elérhető, igy talán rejtve marad
            //file_get_contents fogom használni
            if($csakadmine) {
                echo $admine;
            } else {
                $kimenet .= echoGyakModal();
                echo $kimenet;
            }
            
        } else {
            //itt nem biztos hogy kell bármi tartalmat kiküldeni
            echo "";
        }
    }

    $kapcs->close();

    function echoGyakModal() {
        $str = "";
        $str .= "<button type='button' class='btn btn-default' data-toggle='modal' data-target='#myModal'>Új gyakorlat</button>\n";
        $str .= '<div id="myModal" class="modal fade" role="dialog" style="padding-top: 10px">';
        $str .= '<div class="modal-dialog">';
        $str .= '<div class="modal-content">';
        $str .= '<div class="modal-header">';
        $str .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $str .= '<h4 class="modal-title">Új gyakorlat felvétele</h4>';
        $str .= '</div>';
        $str .= '<div class="modal-body">';
        $str .= '<form id="ujgyakhozzaad" action="#" method="post">';
        $str .= '<div id="hibaDiv"></div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_nev">Gyakorlat neve: </label>';
        $str .= '<input class="form-control" type="text" name="ujgyak_nev" id="ujgyak_nev" />';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_nevHiba">';
        $str .= '    Gyakorlat nevének megadása kötelező! csak betüket és számokat tartalmazhat';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_tipus">Gyakorlat Típusa: </label>';
        $str .= '<select class="form-control" name="ujgyak_tipus" id="ujgyak_tipus">';
        $str .= '<option value="Mell">Mell</option>';
        $str .= '<option value="Hát">Hát</option>';
        $str .= '<option value="Láb">Láb</option>';
        $str .= '<option value="Has">Has</option>';
        $str .= '<option value="Csuklya">Csuklya</option>';
        $str .= '<option value="Bicepsz">Bicepsz</option>';
        $str .= '<option value="Tricepsz">Tricepsz</option>';
        $str .= '<option value="Alkar">Alkar</option>';
        $str .= '<option value="Váll">Váll</option>';
        $str .= '<option value="Vádli">Vádli</option>';
        $str .= '</select>';
        $str .= '<br>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_leiras">Leírás: </label>';
        $str .= '<textarea class="form-control" name="ujgyak_leiras" id="ujgyak_leiras" cols="40" rows="10"></textarea>';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_leirasHiba">';
        $str .= '    Ha kitöltöd, speciális karaktereket nem tartalmazhat!';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<label class="form-control" for="ujgyak_video">Video Link/ID: </label>';
        $str .= '<input type="text" class="form-control" name="ujgyak_video" id="ujgyak_video" />';
        $str .= '<br>';
        $str .= '<span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_videoHiba">';
        $str .= '        Ha kitöltöd, speciális karaktereket nem tartalmazhat!';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '<div class="form-group">';
        $str .= '<button class="btn btn-default" type="button" onclick="hozzadas()">Felvesz</button>';
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
?>