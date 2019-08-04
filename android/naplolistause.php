<?php
    require_once("../adbcuccok.inc.php");
    require_once("NaploLista.class.php");
    require_once("UserManage.class.php");

    if(!isset($_SESSION)) {
        session_start();
    }

    header("Content-Type: application/json; Accept-Charset: UTF-8");


    //majd egyszer android id röggzítése, ellenőrzése,
    //webes felhasználó összekötése az andorid eszközzel
    //jelenleg csak teszt user lesz...
    
    $adat = array();
    $keres = "";

    $naploclass = new NaploLista(ADBSERVER, ADBUSER, ADBPASS, ADBDB);

    if(!$naploclass->initnaplo()) {
        $adat = $naploclass->getHibaUzenet();
        echo json_encode($adat);
        exit;
    }

    $usermanageclass = new UserManage($naploclass);

    //továbbiakban a post ellenörzése, adatok mentése, vagy betöltése
    if(isset($_POST['keres']) && $_POST['keres'] != "") {
        $keres = trim(htmlspecialchars($_POST['keres']));
    }

    switch($keres) {
        case "MENTES":
            if(isset($_POST['job']) && $_POST['job'] != "") {
                if(isset($_POST['userid']) && $_POST['userid'] != "") {
                    $user = trim(htmlspecialchars($_POST['userid']));
                    if($eredmeny = $naploclass->mentes($user,null,json_decode($_POST['job']))) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                } else {
                    if($eredmeny = $naploclass->mentes(null,null,json_decode($_POST['job']))) {
                        $adat["siker"] = "Sikeres mentés!";
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                }
            }
            break;
        case "BEOLVAS":
            if(isset($_POST['datum']) && $_POST['datum'] != "") {
                if(isset($_POST['userid']) && $_POST['userid'] != "") {
                    $user = trim(htmlspecialchars($_POST['userid']));
                    if($eredmeny = $naploclass->getNaploLista($_POST['datum'], $user)) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                } else {
                    if($eredmeny = $naploclass->getNaploLista($_POST['datum'], null)) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                }
            }
            break;
        case "DATUMLISTA" :
            if(isset($_POST['userid']) && $_POST['userid'] != "") {
                $user = trim(htmlspecialchars($_POST['userid']));
                if($eredmeny = $naploclass->getMentesiDatum($user, null)) {
                    $adat = $naploclass->getAdatokVissza();
                } else {
                    $adat = $naploclass->getHibaUzenet();
                }
            } else {
                if($eredmeny = $naploclass->getMentesiDatum(null, null)) {
                    $adat = $naploclass->getAdatokVissza();
                } else {
                    $adat = $naploclass->getHibaUzenet();
                }
            }
            break;
        case "DIAGRAMGET" :
            if(isset($_POST["gyak_idd"]) && $_POST["gyak_idd"] != "") {
                if(isset($_POST['userid']) && $_POST['userid'] != "") {
                    $user = trim(htmlspecialchars($_POST['userid']));
                    if($eredmeny = $naploclass->getGyakDiagramAdat($user, $_POST['gyak_idd'])) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                } else {
                    if($eredmeny = $naploclass->getGyakDiagramAdat(null, $_POST['gyak_idd'])) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
                }
            }
            break;
        case "USERREG" : 
            if(isset($_POST['user']) && $_POST['user'] != "") {
                $json = json_decode($_POST['user']);
                //$usermanageclass->loadFromJson($json);
                if($usermanageclass->saveUser($json)) {
                    $adat = $usermanageclass->getEredmenyVissza();
                } else {
                    $adat = $usermanageclass->getHibaUzenet();
                }
            }
            break;
        case "GETUSER" :
            if(isset($_POST['useraid']) && $_POST['useraid'] != "") {
                if($usermanageclass->getUser($_POST['useraid'])) {
                    $adat = $usermanageclass->getEredmenyVissza();
                } else {
                    $adat = $usermanageclass->getHibaUzenet();
                }
            }
            break;
        case "DELNAPLO" :
            if( (isset($_POST['userid']) && $_POST['userid'] != "") &&
                (isset($_POST['deldate']) && $_POST['deldate'] != "")) {
                    if($naploclass->delNaplo($_POST['userid'], $_POST['deldate'])) {
                        $adat = $naploclass->getAdatokVissza();
                    } else {
                        $adat = $naploclass->getHibaUzenet();
                    }
            }
            break;
        case "GETWEBUSER" :
            if((isset($_POST['useraid']) && $_POST['useraid'] != "") &&
                (isset($_POST['pw']) && $_POST['pw'] != "") ) {
                if($usermanageclass->getWebUser($_POST['useraid'], $_POST['pw'])) {
                    $adat = $usermanageclass->getEredmenyVissza();
                } else {
                    $adat = $usermanageclass->getHibaUzenet();
                }
            }
            break;
        case "USERMENT" :
            if(isset($_POST['user']) && $_POST['user'] != "") {
                $json = json_decode($_POST['user']);
                if($usermanageclass->editUser($json)) {
                    $adat = $usermanageclass->getEredmenyVissza();
                } else {
                    $adat = $usermanageclass->getHibaUzenet();
                }
            }
            break;
        case "MARKERS" :
            if(isset($_POST['user']) && $_POST['user'] != "") {
                if($naploclass->getLocationListFromUsers($_POST['user'])) {
                    $adat = $naploclass->getAdatokVissza();
                } else {
                    $adat = $naploclass->getHibaUzenet();
                }
            }
            break;
        default :
            $adat['KERESHIBA'] = "Nem érkezzet adat. ".$keres;
            break;
    }

    function setErrorCode($msg) {
        $code = http_response_code(406);
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol.' '.$code.' '.$msg);
        $this->naploclass->closeMysqlCon();
        unset($usermanageclass);
        unset($naploclass);
        exit;
    }

    $naploclass->closeMysqlCon();
    unset($usermanageclass);
    unset($naploclass);
    
    echo json_encode($adat);
    exit;
?>