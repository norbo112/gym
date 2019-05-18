<?php
    require_once("adbcuccok.inc.php");

    $hirtipusok = array();
    $felhasznalo = "";

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        //echo "kapcs hiba: ".$kapcs->connect_error;
        error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
        exit;
    }

    if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
        $felhasznalo = "ismeretlen";
    } else {
        $felhasznalo = $kapcs->real_escape_string($_COOKIE['felhasznalo']);
    }
    
    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");


    //felépítem a menüment
    $sql = "SELECT hirtipus ,COUNT(id) as hsz FROM webapphirek GROUP BY hirtipus ORDER BY letrehozas DESC";
    if($eredmeny = $kapcs->query($sql)) {
        if($kapcs->affected_rows == 0) {
            $hirtipusok[] = "Nincs";
        } else {
            while($tomb = $eredmeny->fetch_assoc()) {
                $hirtipusok["tipus"][] = $tomb["hirtipus"];
                $hirtipusok["hsznum"][] = $tomb["hsz"];
            }
            
        }
    }

    if(count($hirtipusok) == 0) {
        echo "Jelenleg nincsenek híreim";
        exit;
    }

    //ez mindenképp kell, a hirek panele
    echo '<ul class="nav nav-pills">';
    echo '<li class="active"><a data-toggle="tab" href="#'.HuToEn($hirtipusok["tipus"][0]).'">'.$hirtipusok["tipus"][0].'<span class="badge">'.$hirtipusok["hsznum"][0].'</span></a></li>';
    for($i=1; $i<count($hirtipusok["tipus"]); $i++) {
        echo '<li><a data-toggle="tab" href="#'.HuToEn($hirtipusok["tipus"][$i]).'">'.$hirtipusok["tipus"][$i].'<span class="badge">'.$hirtipusok["hsznum"][$i].'</span></a></li>';
    }
    //echo '<li class="pull-right"><a href="#">Hírek, információk</a></li>';
    echo '</ul>';

    //echo "<div class='tab-content scrolls'>";
    echo "<div class='tab-content'>";
    for($i=0; $i<count($hirtipusok["tipus"]); $i++) {
        $hirtip = $hirtipusok["tipus"][$i];
        $sql = "SELECT id,letrehozas,hirtipus,hircim,hirtartalom FROM webapphirek WHERE hirtipus = '{$hirtip}' ORDER BY letrehozas DESC";
        if($eredmeny = $kapcs->query($sql)) {
            if($kapcs->affected_rows == 0) {
                alert("Jelenleg nincs hír");
            } else {
                $aktive = ($i == 0) ? " active" : "";
                echo '<div id="'.HuToEn($hirtipusok['tipus'][$i]).'" class="tab-pane fade in'.$aktive.'">';
                while($sor = $eredmeny->fetch_assoc()) {
                    panelEpito($sor, $felhasznalo);
                }
                echo '</div>';
                
            }
        } else {
            alert("Hiba történt a hírek olvasásában");
        }
    }
    echo "</div>";
    
    $kapcs->close();

    function webHszSzam($id) {
        $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
        if($kapcs->connect_errno) {
            error_log("MySQL kapcsolódási hiba: ".$kapcs->connect_error);
            return false;
        }

        $sql = "SELECT COUNT(id) as hnum FROM webhirekhsz WHERE webhirid = '{$id}'";
        
        if(!$eredmeny = $kapcs->query($sql)) {
            error_log("Probléma merült fel a hosszászólások számának lekérdezése közben".$kapcs->connect_error); 
            return false;
        }

        if($eredmeny->num_rows > 0) {
            $t0 = $eredmeny->fetch_assoc();
            return $t0["hnum"];
        } else {
            return 0;
        }

        $kapcs->close();
    }

    function alert($szoveg) {
        echo '<div class="alert alert-success alert-dismissible">';
        echo '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
        echo '<strong>'.$szoveg.' !</strong>';
        echo '</div>';
    }

    //evvel csak a tartalmat kezelem, fentebb ciklusban kérem le a megfelelő tipisokat
    //majd azok lesznek a menü elemei, ha egy tipushoz több tartalom tartozik
    //akkor itt listázom ki
    function panelEpito($adat, $felhasznalo) {
        echo '<div class="col-md-12 col-sm-12 col-lg-12 col-xs-12">';
        echo '<div class="panel panel_hirek">';
        echo '<div class="panel-heading"><h3>'.$adat["hircim"].'</h3>';
        
        echo '</div>';
        echo '<div class="panel-body"><h4>'.$adat["hirtartalom"].'</h4>';
        //echo '<div style="display: none" class="hszmegjelenito" id="hszmegjelenito_'.$adat['id'].'">'; //ide akarok rakni némi cumot majd, szerk, hozzászolás stb...
        //echo '</div>';
        echo '</div>';
        echo '<div class="panel-footer" style="padding-bottom: 20px;">'.$adat["letrehozas"];
        if(checkUser($felhasznalo)) {
            echo '<div class="pull-right btn-group">';
            echo '<button class="btn btn-default" onclick="whmod('.$adat['id'].')">Hozzászólás</button>';
            $wszam = webHszSzam($adat['id']);
            if($wszam != 0) {
                echo '<button class="btn btn-default" onclick="whhsz('.$adat['id'].')">'.$wszam.'<span class="spalpha_'.$adat['id'].' glyphicon glyphicon-chevron-down"></span></button>';
            } else {
                echo '<button class="btn btn-default" onclick="whhsz('.$adat['id'].')" disabled>'.$wszam.'<span class="spalpha_'.$adat['id'].' glyphicon glyphicon-chevron-down"></span></button>';
            }
            if(checkAdmine($felhasznalo)) {
                echo '<button class="btn btn-danger" type="button" onclick="delhir('.$adat['id'].')"><span class="glyphicon glyphicon-remove"></span></button>';
            }
            echo "</div>";
        } else {
            echo '<div class="pull-right">';
            echo 'Kérlek jelentkezz be ha hozzászeretnél szólni!';
            echo "</div>";
        }
        echo '</div>';
        echo '<div data-volttog'.$adat['id'].'="0" style="display: none" class="hszmegjelenito" id="hszmegjelenito_'.$adat['id'].'">'; //ide akarok rakni némi cumot majd, szerk, hozzászolás stb...
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    function HuToEn($s)
    {
        $hu=array('/é/','/É/','/á/','/Á/','/ó/','/Ó/','/ö/','/Ö/','/ő/','/Ő/','/ú/','/Ú/','/ű/','/Ű/','/ü/','/Ü/','/í/','/Í/','/ /');
        $en= array('e','E','a','A','o','O','o','O','o','O','u','U','u','U','u','U','i','I','_');
        $r=preg_replace($hu,$en,$s);
        return $r;
    }

    
?>