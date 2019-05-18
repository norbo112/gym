<?php
    function ervenyes_iranyitoszam($iranyitoszam)
    {
        if(!preg_match('/^[\d]+$/', $iranyitoszam)) {
            return false;
        } else if(strlen($iranyitoszam) == 4) {
            return true;
        } else {
            return false;
        }
    }

    function ervenyes_megye($megye)
    {
        $ervenyesMegyek = array("BK","Bar","Bekes","BAZ","Cson","Fejer","GyMS","HB","Pest");
        if(in_array($megye,$ervenyesMegyek)) {
            return true;
        } else {
            //return false;
            return true;
        }
    }

    function ervenyes_telefon($telefonszam)
    {
        if(!preg_match('/^[\d]+$/', $telefonszam)) {
            return false;
        } else if(strlen($telefonszam) < 8) {
            return false;
        } else {
            return true;
        }
    }

    function ervenyes_telefontipus($telefontipus)
    {
        $ervenyesTelefontipusok = array("munka","otthoni");
        if (in_array($telefontipus, $ervenyesTelefontipusok)) {
            return true;
        } else {
            return false;
        }
    }   
?>