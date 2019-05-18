<?php 

function user_exists($username)
{  
  //ellenorzi hogy letezik-e a user  
}

function user_get_encrypted_password($username)
{  
  //hashelt jelszo betoltese  
}

function store_token($username, $token)
{  
  //token eltarolasa  
}

function validate_token($token)
{  
  //Token ellenorzese es usernev visszaadasa  
}

function delete_token($token)
{  
  //Token torlese a DB-bol  
}

function check_password($username, $password)
{
    if (!user_exists($username)) {
        return false;
    }
    $hash = user_get_encrypted_password($username);
    return password_verify($password, $hash);
}

function generate_token()
{
    return hash('sha256', openssl_random_pseudo_bytes(192));
}

switch (isset($_GET['action'])) {
    case 'login';
    if (!check_password($_POST['username'], $_POST['password'])) {  
      //Hibas belepes  
    } else {
        $token = generate_token();
        store_token($_POST['username'], $token);
        setcookie('authtoken', $token);  
      //User belepett  
    }
    break;
case 'logout':
    if (!isset($_COOKIE['authtoken']) ||
        !validate_token($_COOKIE['authtoken'])) {  
      //User nincs belepve, hibaoldal  
    } else {  
      //User kileptetese  
        delete_token($_COOKIE['authtoken']);
    }
    break;
default:
    if (!isset($_COOKIE['authtoken']) ||
        !validate_token($_COOKIE['authtoken'])) {  
      //User nincs belepve, hibaoldal vagy redirect a login oldalra  
    } else {  
      //Betoltjuk az oldalt amihez jelszo szukseges  
    }
    break;
} 


