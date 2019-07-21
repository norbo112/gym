<?php
    require_once("UserManage.class.php");
    if(!isset($_SESSION)) {
        session_start();
    }
/**
 * NaploLista osztály, mentés és betöltésre hálozaton keresztül
 * legföbbképp a java nyelben irt json adatokat dolgozza fel
 */
class NaploLista
{
    private $mysql = null;
    private $hibauzenet = null;
    private $adatokvissza = null;
    private $gyaktomb = null;
    private $mentesuzenet = null;
    private $tesztuser = null;

    private $adbuser, $adbpass, $adbserver, $adbdb;

    private $mentesidatum = null;

    public function __construct($server, $user, $pass, $database)
    {
        $this->hibauzenet = array();
        $this->adatokvissza = array();
        $this->mentesuzenet = "";
        $this->mentesidatum = date("Y-m-d H:i:s");
        $this->tesztuser = "tg.sures@gmail.com";

        $this->adbuser = $user;
        $this->adbpass = $pass;
        $this->adbserver = $server;
        $this->adbdb = $database;
        
    }

    public function initnaplo() {
        $this->mysql = new mysqli($this->adbserver, $this->adbuser, $this->adbpass, $this->adbdb);
        if ($this->mysql->connect_errno) {
            $this->hibauzenet[] = "MySQL kapcsolódási hiba: " .
                $this->mysql->connect_error;
            return false;
        } else {
            $this->mysql->query("SET NAMES 'UTF8'");
            $this->mysql->query("SET CHARACTER SET 'UTF8'");

            $this->gyaktomb = $this->getGyakTomb();
        }

        return true;
    }

    public function getMysql() {
        return $this->mysql;
    }

    public function setTestUser($testuser) {
        $this->tesztuser = $testuser;
    }

    public function getNaploLista($vandatum, $felhasznalo)
    {
        if ($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        $pregt = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/u';
        if (preg_match($pregt, $vandatum)) {
            $sql = "SELECT mentesidatum, gyakrogzitesiidopont ,megnevezes, megjegyzes, gyakorlat.gyak_id as azon, gyakorlat.csoport as csop FROM naplo, gyakorlat WHERE felhasznalo = '{$felhasznalo}' " .
                "AND gyakorlat.gyak_id = naplo.gyak_id AND mentesidatum = '{$vandatum}' ORDER BY gyakrogzitesiidopont ASC";
        } else {
            $this->hibauzenet['naplolistahiba'] = "Érvénytelen adat: " . $vandatum;
            return false;
        }

        if (!($eredmeny = $this->mysql->query($sql))) {
            $this->hibauzenet['naplolistahiba'] = "Hiba a lekérdezésben: " . $this->mysql->error;
            error_log("hiba a lekérdezésben " . $this->mysql->error);
            return false;
        } else {
            if (preg_match($pregt, $vandatum)) {
                $note = "";
                $sql = "SELECT * FROM naplonote WHERE felhasznalo = '{$felhasznalo}' AND mentesidatum = '{$vandatum}'";
                if ($e = $this->mysql->query($sql)) {
                    if ($e->num_rows > 0) {
                        $erre = $e->fetch_assoc();
                        $this->adatokvissza["naplonote"] = $erre["notenotes"];
                    } else {
                        $this->adatokvissza["naplonote"] = "Nincs megjegyzés hozzáfűzve a naplóhoz";
                    }
                } else {
                    $this->hibauzenet['naplolistahiba'] = "Hiba történt a note lekérésében: " .
                        $this->mysql->error;
                    return false;
                }

                while ($tomb = $eredmeny->fetch_assoc()) {
                    $sorismtomb = $this->getSorIsmTomb($tomb['azon'], $tomb['mentesidatum'], $felhasznalo);
                    if(isset($sorismtomb['hiba'])) {
                        $this->hibauzenet['naplolistahiba'] = "Hiba történt a sorozat lekérdezése közben ".$sorismtomb['hiba'];
                        return false;
                    }

                    $this->adatokvissza["gyaksik"][] = array(
                        "RogzitesIdopont" => $tomb['gyakrogzitesiidopont'],
                        "Name" => $tomb['megnevezes'],
                        "Megjegyzes" => $tomb['megjegyzes'],
                        //"gycsoport" => $tomb['csop'],
                        "sorozat" => $sorismtomb
                    );
                }
            }
            return true;
        }
    }

    /**
     * Feltölti a megadott felhasználó és mentési dátum szerinti napló note-ot
     * @var $datum napló mentési dátuma
     * @var $felhasznalo adott felhasználó idje, android id
     */
    public function getNaploNote($felhasznalo, $datum) {
        $pregt = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/u';
        $datum = $this->mysql->real_escape_string($datum);
        if($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        if($datum == "" || $datum == null) {
            $this->hibauzenet['naplonotehiba'] = "Nincs dátum megadva";
            return false;
        }
        
        if(!preg_match($pregt, $datum)) {
            $this->hibauzenet['naplonotehiba'] = "Nem megfelelő dátumformátumot adtál meg ".$datum;
            return false;
        }

        $sql = "SELECT * FROM naplonote WHERE felhasznalo = '{$felhasznalo}' AND mentesidatum = '{$datum}'";
        if ($e = $this->mysql->query($sql)) {
            if ($e->num_rows > 0) {
                $erre = $e->fetch_assoc();
                $this->adatokvissza["naplonote"] = $erre["notenotes"];
            } else {
                $this->adatokvissza["naplonote"] = "Nincs megjegyzés hozzáfűzve a naplóhoz";
            }
        } else {
            $this->adatokvissza["naplonotehiba"] = "Hiba történt a note lekérésében";
        }

        return true;

    }

    public function getMentesiDatum($felhasznalo, $gyakid) {
        if($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        if($gyakid != null) {
            $sql = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}' AND gyak_id = '{$gyakid}' GROUP BY mentesidatum ORDER BY mentesidatum DESC";
        } else {
            $sql = "SELECT mentesidatum FROM naplo WHERE felhasznalo = '{$felhasznalo}' GROUP BY mentesidatum ORDER BY mentesidatum DESC";
        }
            
        if(!($eredmeny = $this->mysql->query($sql))) {
            $this->hibauzenet['mentesidatumhiba'] = " Hiba a lekérdezésben ".$this->mysqli->connect_error;
            error_log("hiba a lekérdezésben ".$this->mysqli->error);
            return false;
        } else {
            if($eredmeny->num_rows == 0) {
                $this->hibauzenet['mentesidatumhiba'] = "Nincsenek mentett naplók a megadott felhasználóhoz: ".$felhasznalo;
                return false;
            }
            while($tomb = $eredmeny->fetch_assoc()) {
                $this->adatokvissza["mentesidatum"][] = $tomb["mentesidatum"];
            }
        }
        return true;
    }

    //diagram adatok, az adott mentésben szereplő gyakorlatról
    //mentési dátum és gyakorat azonosító alapján
    public function getGyakDiagramAdat($felhasznalo, $gyakid) {
        if($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        if(!($this->getMentesiDatum($felhasznalo, $gyakid))) {
            $this->hibauzenet['gyakdiagramhiba'] = "Sikertelen mentési dátum kérés";
            return false;
        }

        //$gyakidd = mysqli_real_escape_string($this->mysql, $gyakid);

        for ($i = 0; $i < count($this->adatokvissza['mentesidatum']); $i++) {
            
            
            $sorozat_sajat = $this->getSorIsmTomb($gyakid, $this->adatokvissza['mentesidatum'][$i], $felhasznalo);
            if(isset($sorozat_sajat['hiba'])) {
                $this->hibauzenet['gyakdiagramhiba'] = "Sorozat lekérdezés hiba \n" . $sorozat_sajat['hiba'];
                return false;
            }

            $osszsuly = $this->getOsszsuly($felhasznalo, $this->adatokvissza['mentesidatum'][$i], $gyakid);
            if (isset($osszsuly["hiba"])) {
                $this->hibauzenet['gyakdiagramhiba'] = "Összsúly lekérdezés hiba \n" . $osszsuly['hiba'];
                return false;
            }

            $this->adatokvissza['resgyakdiagram'][] = array(
                "mentesidatum" => $this->adatokvissza['mentesidatum'][$i],
                "osszsuly" => $osszsuly["osszsuly"],
                "sorozat" => $sorozat_sajat
            );
        }

        return true;
    }

    function getOsszsuly($felhasznalo, $mentesi, $gyakid) {
        $res = array();
        $adat = 0;

        $sql = "SELECT suly, ism FROM sorozat WHERE felhasznalo = '{$felhasznalo}' AND ".
            "mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakid}'";

        if(!($eredmeny = $this->mysql->query($sql))) {
            $res["hiba"] = "Probléma a lekérdezésben: ".$this->mysql->error;
        } else {
            while($e = $eredmeny->fetch_assoc()) {
                $adat += $e["suly"] * $e["ism"];
            }

            $res["osszsuly"] = $adat;
        }

        return $res;
    }

    private function getSorIsmTomb($gyakorlat_id, $mentesi, $felhasznalo) 
    {
        if($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        $sql_sorozat = "SELECT suly,ism,ismidopont FROM sorozat WHERE ".
            "felhasznalo = '{$felhasznalo}' AND mentesidatum = '{$mentesi}' AND gyak_id = '{$gyakorlat_id}'";
        $res = array();

        if(!($eredmeny_sor = $this->mysql->query($sql_sorozat))) {
            $res["hiba"] = "Hiba a sorozat lekérdezésben".$this->mysql->connect_error;
            error_log("Hiba a sorozat lekérdezésben".$this->mysql->connect_error);
        } else if($eredmeny_sor->num_rows == 0) {
            $res["hiba"] = "Üres a sorozat tároló, vagy az adatok nem léteznek";
        } else {
            while($tomb = $eredmeny_sor->fetch_assoc()) {
                $res["Suly"][] = $tomb['suly'];
                $res["Ism"][] = $tomb['ism'];
                $res["IsmRogzitesIdopontja"][] = $tomb['ismidopont'];
            }
        }
        return $res;
    }

    public function getTestHibaMentesiDatum() 
    {
        $res['mentesidatumhiba'] = "Test hiba érkezett";
        return $res;
    }

    public function mentes($felhasznalo, $mentesiIdopont, $sajattomb)
    {
        if ($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        if($mentesiIdopont == null) {
            $mentesiIdopont = $this->mentesidatum;
        }

        $secureEmail = $this->mysql->real_escape_string($felhasznalo);
        

        //ciklussal fogom megoldani a mysql-be való adatmentést
        //összehozva a javabol küldött adatokkal gyaksik néven a tömb
        for($i=0; $i<count($sajattomb->gyaksik); $i++) {
            $gyakrogzido = $this->mysql->real_escape_string($sajattomb->gyaksik[$i]->RogzitesIdopont);
            $gyakmegjegyzes = $this->mysql->real_escape_string($sajattomb->gyaksik[$i]->Megjegyzes);

        $sqlNaplo = "INSERT INTO naplo (felhasznalo, mentesidatum, gyakrogzitesiidopont, gyak_id, megjegyzes) VALUES ".
                    "('{$secureEmail}','{$mentesiIdopont}','{$gyakrogzido}', ".
                    "'{$this->gyaktomb[$sajattomb->gyaksik[$i]->Name]}', ".
                    "'{$gyakmegjegyzes}') ";
            if(!$this->mysql->query($sqlNaplo)) {
                $this->hibauzenet['mentesihiba'] = "Hiba a napló beszurásakor".$this->mysql->error;
                error_log("Hiba a napló beszúrásakor: ".$this->mysql->error);
                return false;
            }
        }
    
        
        for($i=0; $i<count($sajattomb->gyaksik); $i++) {
            for($k=0; $k<count($sajattomb->gyaksik[$i]->Suly); $k++) {
                $sqlSorozat = "INSERT INTO sorozat (felhasznalo, mentesidatum, gyak_id, suly, ism, ismidopont ) VALUES".
                    "('{$secureEmail}','{$mentesiIdopont}',".
                    "'{$this->gyaktomb[$sajattomb->gyaksik[$i]->Name]}',".
                    "'{$sajattomb->gyaksik[$i]->Suly[$k]}', '{$sajattomb->gyaksik[$i]->Ism[$k]}', ".
                    "'{$sajattomb->gyaksik[$i]->IsmRogzitesIdopontja[$k]}')";
                if(! ($this->mysql->query($sqlSorozat))) {
                    $this->hibauzenet['mentesihiba'] = "Sikertelen sorozat tábla frissítés".$this->mysqli->error;
                    error_log("Hiba a sorozat beszúrásakor: ".$this->mysql->error);
                    return false;
                }
            }
        }

        $this->adatokvissza['siker'] = "Sikeres mentés!";
        return true;
    }

    private function getGyakTomb() {
        $gyaktomb = array();

        $sql = "SELECT gyak_id, megnevezes FROM gyakorlat";
        if(!$eredmeny = $this->mysql->query($sql)) {
            $this->hibauzenet[] = "Hiba a gyakorlatok lekérdezésében: ".$this->mysqli->error;
        } else {
            if($eredmeny->num_rows == 0) {
                return false;
            } else {
                while($sor = $eredmeny->fetch_assoc()) {
                    $gyaktomb[$sor['megnevezes']] = $sor['gyak_id'];
                }

                $eredmeny->free_result();
                return $gyaktomb;
            }
        }
    }

    function saveNaplonote($felhasznalo,$mentesiIdopont,$naplonote) {
        if($naplonote == "") {
            $naplonote = "";
        }

        if($felhasznalo == null) {
            $felhasznalo = $this->tesztuser;
        }

        if($mentesiIdopont == "" || $mentesiIdopont == null) {
            $mentesiIdopont = $this->mentesidatum;
        }

        //naplonote hozzáadása a naplonote táblához
        $secureEmail = $this->mysql->real_escape_string($felhasznalo);
        if(isset($naplonote)) {
            $notee = $this->mysql->real_escape_string($naplonote);
            $sql = "INSERT INTO naplonote (felhasznalo, mentesidatum, notenotes) ".
                   "VALUES ('{$secureEmail}', '{$mentesiIdopont}', '{$notee}')";

            if(!$this->mysql->query($sql)) {
                $this->hibauzenet[] = "Sikertelen naplonote hozzáadás ".$this->mysql->connect_error;
                return false;
            }
        }

        return true;
    }

    /**
     * Adatok törlése
     */
    public function delNaplo($felhasznalo, $mentesidatum)
    {
        $deldate = $this->mysql->real_escape_string($mentesidatum);
        if(!UserManage::checkUser($felhasznalo, "ok")) {
            $this->hibauzenet['falseuser'] = "Nem létezik a felhasználó";
            return false;
        }

        $sql = "DELETE FROM naplo WHERE mentesidatum = '{$deldate}' AND felhasznalo = '{$felhasznalo}'";
        if(!($eredmeny = $this->mysql->query($sql))) {
            $this->hibauzenet["hiba"] = " Hiba a lekérdezésben ".$this->mysql->error;
            return false;
        } else {
            if($this->mysql->affected_rows != 0) {
                $this->adatokvissza["naplo_torolve"] = $this->mysql->affected_rows." napló rekord törölve";
                $sqlsorozat = "DELETE FROM sorozat WHERE mentesidatum = '{$deldate}' AND felhasznalo = '{$felhasznalo}'";
                if($this->mysql->query($sqlsorozat)) {
                    $this->adatokvissza['sorozat_torolve'] = "A naplóhoz tartozó sorozatok törölve";
                } else {
                    $this->hibauzenet['hiba'] = "Sikertelen sorozat törtlés";
                    return false;
                }

                return true;
            } else {
                $this->hibauzenet['hiba'] = "Nem sikerült törölni az adatokat";
                return false;
            }
        }
    }

    public function closeMysqlCon() {
        $this->mysql->close();
        $this->mysql = null;
    }

    public function getHibaUzenet()
    {
        return $this->hibauzenet;
    }

    public function getHibaLength() {
        return count($this->hibauzenet);
    }

    public function getAdatokVissza() {
        return $this->adatokvissza;
    }
}
?>