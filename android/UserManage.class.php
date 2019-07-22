<?php
    //require_once("../adbcuccok.inc.php");
    require_once("NaploLista.class.php");
    if(!isset($_SESSION)) {
        session_start();
    }

    /**
     * Felhasználót regisztráló, kezelő osztály, 
     * mely szorosan együtt használatos a NaploLista osztályal
     */
    class UserManage
    {
        private $naploLista = null;
        private $hibauzenet = null;
        private $eredmenyvissza = null;
        private $mysqli = null;
        //ha webes userrol van szó


        public function __construct($pNaploList)
        {
            $this->naploLista = $pNaploList;
            $this->hibauzenet = array();
            $this->eredmenyvissza = array();
            $this->mysqli = $this->naploLista->getMysql();
        }

        public function init() {
            /*if(! $this->naploLista->init()) {
                $this->hibauzenet['usermanage'] = $this->naploLista->getHibaUzenet();
                return false;
            }*/

            $this->mysqli = $this->naploLista->getMysql();
            //$this->mysqli->query("SET NAMES 'UTF8'");
            //$this->mysqli->query("SET CHARACTER SET 'UTF8'");

            return true;
        }

        /*public function loadFromJson($jsonobj) {
            $this->nev = $jsonobj->au_nev;
            $this->randomcode = $jsonobj->au_randomcode;
            $this->androidid = $jsonobj->au_androidid;
            $this->longitude = $jsonobj->au_long;
            $this->latitude = $jsonobj->au_lat;
            $this->altitude = $jsonobj->au_alt;
        }*/

        public function saveUser($jsonobj) {
            //hiba ellenörzéstől most eltekuntek
            //ellenörzés létezik -e a felhasználó
            $sql = "SELECT au_androidID FROM androiduser WHERE au_androidID = '{$this->androidid}'";

            if($usercheck = $this->mysqli->query($sql)) {
                $user = $usercheck->fetch_assoc();
                if(isset($user['au_androidID']) && $user['au_androidID'] != "") {
                    $this->hibauzenet['AU_letezik'] = "Már regisztráltál evvel a készülékkel";
                    return false;
                }
            }

            $vezetekNev = mysqli_real_escape_string($this->mysqli, $jsonobj->au_vnev);
            $keresztNev = mysqli_real_escape_string($this->mysqli, $jsonobj->au_knev);
            $email = mysqli_real_escape_string($this->mysqli, $jsonobj->au_email);
            $jelszo = mysqli_real_escape_string($this->mysqli, $jsonobj->au_jelszo);
            $orzag = mysqli_real_escape_string($this->mysqli, $jsonobj->au_orszag);
            $megye = mysqli_real_escape_string($this->mysqli, $jsonobj->au_megye);
            $varos = mysqli_real_escape_string($this->mysqli, $jsonobj->au_varos);
            $cim = mysqli_real_escape_string($this->mysqli, $jsonobj->au_cim);
            $iranyitoszam = mysqli_real_escape_string($this->mysqli, $jsonobj->au_irsz);
            $suly = mysqli_real_escape_string($this->mysqli, $jsonobj->au_suly);
            $magassag = mysqli_real_escape_string($this->mysqli, $jsonobj->au_magas);
            $maxfek = mysqli_real_escape_string($this->mysqli, $jsonobj->au_mfek);
            $maxgugg = mysqli_real_escape_string($this->mysqli, $jsonobj->au_mgugg);
            $felhuzocsucs = mysqli_real_escape_string($this->mysqli, $jsonobj->au_mfelh);
            $genre = mysqli_real_escape_string($this->mysqli, $jsonobj->au_genre);
            $latlong = mysqli_real_escape_string($this->mysqli, $jsonobj->au_long);
            $latlat = mysqli_real_escape_string($this->mysqli, $jsonobj->au_lat);
            $latalt = mysqli_real_escape_string($this->mysqli, $jsonobj->au_alt);
            $aid = mysqli_real_escape_string($this->mysqli, $jsonobj->au_androidid);

            $sql = "INSERT INTO felhasznalo (vnev,knev,email,jelszo,orszag,megye,varos,cim,iranyitoszam,suly,maxfek,maxgugg,letrejott,maxfelhuz, magassag, genre,".
                      "au_loc_long, au_loc_lat, au_loc_alt, androidId)".
                      " VALUES ('{$vezetekNev}','{$keresztNev}','{$email}','{$jelszo}','{$orzag}','{$megye}','{$varos}','{$cim}','{$iranyitoszam}'".
                      ",'{$suly}','{$maxfek}','{$maxgugg}',NOW(),'{$felhuzocsucs}','{$magassag}','{$genre}',".
                      "'{$latlong}', '{$latlat}', '{$latalt}', '{$aid}')";

            if ($this->mysqli->query($sql)) {
                $azon = $this->mysqli->insert_id;
                $this->eredmenyvissza['AU_sikeres'] = "Felhasználó {$email}, {$aid} fiók felvéve";
                return true;
            } else {
                error_log("Android beszúrása sikertelen" . $this->mysqli->error);
                $this->hibauzenet['AU_sikertelen'] = "Android beszúrása sikertelen";
                return false;
            }
        }

        public function getUser($useraid) {
            $sql = "SELECT * FROM androiduser WHERE au_androidID = '{$useraid}'";
            if($res = $this->mysqli->query($sql)) {
                if($res->num_rows != 0) {
                    while($sor = $res->fetch_assoc()) {
                        $this->eredmenyvissza['androiduser'] = array(
                            "au_nev" => $sor['au_Nev'],
                            "au_androidid" => $sor['au_androidID'],
                            "au_randomcode" => $sor['au_randomCode'],
                            "au_long" => $sor["au_loc_long"],
                            "au_lat" => $sor["au_loc_lat"],
                            "au_alt" => $sor["au_loc_alt"]
                        );
                    }

                    return true;
                } else {
                    $this->hibauzenet['AU_nincs'] = "Nem létezik a felhasználó";
                    return false;
                }
            } else {
                $this->hibauzenet['AU_hiba'] = "Mysql hiba: ".$this->mysqli->error;
                return false;
            }
        }

        public function getEredmenyVissza() { 
            return $this->eredmenyvissza;
        }

        public function getHibaUzenet() { 
            return $this->hibauzenet;
        }

        public function checkUser($felhasznalo, $webuser) {
            if($felhasznalo == null) {
                return false;
            }

            $mysql = new mysqli(ADBSERVER, ADBUSER, ADBPASS, ADBDB);
            if($mysql->connect_errno) {
                error_log("checkUser: Kapcsolódási hiba".$mysql->connect_error);
                return false;
            }
            $mysql->query("SET NAMES UTF8");
            $mysql->query("SET CHARACTER SET UTF8");

            $biztFelh = $mysql->real_escape_string($felhasznalo);

            if($webuser != null) {
                $sql = "SELECT email FROM felhasznalo WHERE email = '{$biztFelh}'";
            } else {
                $sql = "SELECT au_androidID FROM androiduser WHERE au_androidID = '{$felhasznalo}'";
            }

            if($usercheck = $mysql->query($sql)) {
                $user = $usercheck->fetch_assoc();
                if($webuser != null) {
                    $vajon = isset($user['email']) && $user['email'] != "";
                } else {
                    $vajon = isset($user['au_androidID']) && $user['au_androidID'] != "";
                }

                if($vajon) {
                    return true;
                }
            }

            $mysql->close();
            return false;
        }

        public function getWebUser($felhasznalo, $jelszo) {
            if(isset($_COOKIE['nevem']) && $_COOKIE['nevem'] != "") {
                $this->hibauzenet['USERLIVE'] = "Már beléptél a rendszerbe ".$_COOKIE['nevem'];
                return false;
            }

            if(!$this->checkUser($felhasznalo, "van")) {
                $this->hibauzenet['GETUSER_HIBA'] = "Nem létezik a felhasználó";
                return false;
            }
            $mysql = new mysqli(ADBSERVER, ADBUSER, ADBPASS, ADBDB);
            if($mysql->connect_errno) {
                $this->hibauzenet['GETUSER_HIBA'] = "Kapcsolódási hiba: ".$mysql->connect_error;
                return false;
            }
            $mysql->query("SET NAMES UTF8");
            $mysql->query("SET CHARACTER SET UTF8");

            $biztFelh = $mysql->real_escape_string($felhasznalo);
            $biztJelszo = $mysql->real_escape_string($jelszo);

            $sql = "SELECT * FROM felhasznalo WHERE email = '{$biztFelh}'";
            $eredmeny = $mysql->query($sql);
            if(!$eredmeny) {
                $this->hibauzenet['GETUSER_HIBA'] = "Lekérdezés hiba: ".$mysql->connect_error;
                return false;
            }

            $sor = $eredmeny->fetch_assoc();
            $dbjelszo = $sor['jelszo'];

            if(!password_verify($biztJelszo, $dbjelszo)) {
                $this->hibauzenet['GETUSER_HIBA_PASS'] = "Nem egyezik a jelszó";
                return false;
            }

            //itt elkészítem a visszaküldendő felhasználó adatokat
            //amibol az AndroidUser-t előállítom
            $this->eredmenyvissza['AndroidUser'] = array(
                "azonosito"=>$sor["azonosito"],
                "vnev"=>$sor["vnev"],
                "knev"=>$sor["knev"],
                "email"=>$sor["email"],
                "orszag"=>$sor['orszag'],
                "megye"=>$sor["megye"],
                "varos"=>$sor["varos"],
                "cim"=>$sor["cim"],
                "iranyitoszam"=>$sor["iranyitoszam"],
                "suly"=>$sor["suly"],
                "maxfek"=>$sor["maxfek"],
                "maxgugg"=>$sor["maxgugg"],
                "letrejott"=>$sor["letrejott"],
                "maxfelhuz"=>$sor["maxfelhuz"],
                "magassag"=>$sor["magassag"],
                "genre"=>$sor["genre"],
                "au_loc_long"=>$sor["au_loc_long"],
                "au_loc_lat"=>$sor["au_loc_lat"],
                "au_loc_alt"=>$sor["au_loc_alt"],
                "androidId"=>$sor['androidId']
            );
            
            
            $this->eredmenyvissza['GETUSER_OK'] = "Sikeres belépés!";
            
            setcookie("felhasznalo",$sor['email'], time()+60*60*3);
            setcookie("nevem", $sor['knev'], time()+60*60*3);

            return true;
        }
    }
