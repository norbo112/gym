<?php
    require_once("adbcuccok.inc.php");
    
    if(session_id() == '') {
        session_start();
    }

    foreach($_SESSION as $kulcs => $ertek) {
        $_SESSION[$kulcs] = "";
        unset($_SESSION[$kulcs]);
    }        

    $_SESSION = array();
    if(ini_get("session.use_cookies")) {
        $sutiParameterek = session_get_cookie_params();
        setcookie(session_name(),'', time() - 28800,
        $sutiParameterek['path'],$sutiParameterek['domain'],
        $sutiParameterek['secure'],$sutiParameterek['httponly']);
    }

    if(session_destroy()) {
        header("Location: index.html");
    } else {
        echo "Sikertelen kijelentkezés";
    }
?>