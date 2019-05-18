<?php
    require_once("adbcuccok.inc.php");

    $izomcsoport = array();
    $gyakorlatok = array();
    $select_elem = "";

    if(isset($_POST["kapcs"]) && strlen($_POST["kapcs"]) < 2) {
        $masodik = $_POST["kapcs"];
    } else {
        $masodik = false;
    }

    $kapcs = new mysqli(ADBSERVER,ADBUSER,ADBPASS,ADBDB);
    if($kapcs->connect_errno) {
        echo "MySQL kapcsolódási hiba".$kapcs->connect_error;
        exit;
    }

    mysqli_query($kapcs,"SET NAMES 'UTF8'");
    mysqli_query($kapcs,"SET CHARACTER SET 'UTF8'");

    //ez a rész ahhoz hogy csak a gyakorlatokat akarom elkészíteni izomcsoportok szerint
    if($masodik == 2 && isset($_POST["izomcsoport"])) {
        $p_izomcsop = $kapcs->real_escape_string($_POST["izomcsoport"]);
        $gyakorlatok = getGyakFromCsoport($kapcs, $p_izomcsop);
        if(!isset($gyakorlatok["hiba"])) {
            $gy = $gyakorlatok["mn"];
            
            for($i=0; $i<count($gy); $i++) {
                $select_elem .= "<option value='{$gy[$i]}'>{$gy[$i]}</option>\n";
            }
        } else {
            if($p_izomcsop == "Pihenőnap") {
                $select_elem .= "<option value='Pihi van'>Pihi van</option>\n";
            } else {
                $select_elem .= "<option value='0'>Válasz csoportot</option>\n";
            }
            
        }
        echo "<label class='form-control' for='gyaksik_icsop'>Gyakorlatok: </label>";
        echo '<select class="form-control" name="gyaksik_icsop" id="gyaksik_icsop">';
        echo $select_elem;
        echo '</select>';
        exit;
    } 

    $select_elem .= "<option>Kérlek, válassz...</option>";

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
        $sql = "SELECT megnevezes, gyak_id FROM gyakorlat WHERE csoport = '{$izomcsoport[$i]}'";

        if($eredmeny = $kapcs->query($sql)) {
            
            if(!$masodik) {
                $select_elem .= "<optgroup label='{$izomcsoport[$i]}'>\n";
                while($sor = $eredmeny->fetch_assoc()) {
                    $select_elem .= "<option value='".$sor['megnevezes']."'>".$sor['megnevezes']."</option>\n";
                }
                $select_elem .= "</optgroup>\n";
            } else if($masodik == 1) {
                $select_elem .= "<option value='{$izomcsoport[$i]}'>{$izomcsoport[$i]}</option>\n";
            } else if($masodik == 5) {
                //ez kell a korábbi naplók oldalon lévő diagramhoz, value a gyakid lesz
                $select_elem .= "<optgroup label='{$izomcsoport[$i]}'>\n";
                while($sor = $eredmeny->fetch_assoc()) {
                    $select_elem .= "<option value='".$sor['gyak_id']."_".$sor['megnevezes']."'>".$sor['megnevezes']."</option>\n";
                }
                $select_elem .= "</optgroup>\n";
            }

        } else {
            echo "Hiba az adatbázis elérésében";
        }
    }

    if($masodik == 1) {
        echo "<label class='form-control' for='izomcsoportok'>Izomcsop: </label>";
        echo '<select class="form-control" name="izomcsoportok" id="izomcsoportok">';
        $select_elem .= "<option value='Pihenőnap'>Pihenőnap</option>\n";
        echo $select_elem;
        echo '</select>';
    } else if($masodik == 5) {
        //diagramhoz használható saját elem_id
        echo "<label class='form-control' for='gyNameDig'>Gyakorlat: </label>";
        echo '<select class="form-control" name="gyNameDig" id="gyNameDig">';
        echo $select_elem;
        echo '</select>';
    } else {
        echo "<label class='form-control' for='gyName'>Gyakorlat: </label>";
        echo '<select class="form-control" name="gyName" id="gyName">';
        echo $select_elem;
        echo '</select>';
    }
    
    function getGyakFromCsoport($kapcs, $izomcsop) {
        $sql = "SELECT megnevezes FROM gyakorlat WHERE csoport = '{$izomcsop}'";
        $res = array();
        if(!($e = $kapcs->query($sql))) {
            $res["hiba"] = false;
            return $res;
        } else {
            if($e->num_rows != 0) {
                while($er = $e->fetch_assoc()) {
                    $res["mn"][] = $er["megnevezes"];
                }
            } else {
                $res["hiba"] = false;
            }
            
        }
        return $res;
    }
?>