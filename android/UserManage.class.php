<?php
    //require_once("../adbcuccok.inc.php");
    require_once("NaploLista.class.php");

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

        //saját user adatok, amiket menteni kell
        private $nev = null;
        private $randomcode = null;
        private $androidid = null;
        private $longitude = null;
        private $latitude = null;
        private $altitude = null;

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
            $this->nev = $jsonobj->au_nev;
            $this->randomcode = $jsonobj->au_randomcode;
            $this->androidid = $jsonobj->au_androidid;
            $this->longitude = $jsonobj->au_long;
            $this->latitude = $jsonobj->au_lat;
            $this->altitude = $jsonobj->au_alt;

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

            $e_nev = mysqli_real_escape_string($this->mysqli, $this->nev);
            $e_rcode = mysqli_real_escape_string($this->mysqli, $this->randomcode);
            $e_aid = mysqli_real_escape_string($this->mysqli, $this->androidid);
            $e_lat = mysqli_real_escape_string($this->mysqli, $this->latitude);
            $e_long = mysqli_real_escape_string($this->mysqli, $this->longitude);
            $e_alt = mysqli_real_escape_string($this->mysqli, $this->altitude);

            $sql = "INSERT INTO androiduser (au_Nev, au_randomCode, au_androidID, au_loc_lat, au_loc_long, au_loc_alt) ".
                    "VALUES ('{$e_nev}', '{$e_rcode}', '{$e_aid}', '{$e_lat}', '{$e_long}', '{$e_alt}')";

            if ($this->mysqli->query($sql)) {
                $azon = $this->mysqli->insert_id;
                $this->eredmenyvissza['AU_sikeres'] = "Android {$e_aid} fiók beszúrva {$azon} azonosítóval";
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

        public static function checkUser($felhasznalo, $webuser) {
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
                $sql = "SELECT knev, email, jelszo FROM felhasznalo WHERE email = '{$biztFelh}'";
                $vajon = isset($webuser['email']) && $webuser['emal'] != "";
            } else {
                $sql = "SELECT au_androidID FROM androiduser WHERE au_androidID = '{$felhasznalo}'";
                $vajon = isset($webuser['au_androidID']) && $webuser['au_androidID'] != "";
            }



            if($usercheck = $mysql->query($sql)) {
                $user = $usercheck->fetch_assoc();
                if($vajon) {
                    return true;
                }
            }

            $mysql->close();
            return false;
        }
    }
?>