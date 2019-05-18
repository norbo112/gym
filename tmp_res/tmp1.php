
<?php 
//ip cimhez
$ip = '192.168.1.100'; // Minta IP cím
//  Az eredeti IP címet vágja és ebben a formában adja vissza:  XXX.XXX.XXX.0
function trimIP($ip) {
 $pos = strrpos($ip, '.');
 if ($pos !== false) { $ip = substr($ip, 0, $pos+1); }
 return $ip . '.0';
}
$ip = trimIP($ip);

/**
 * Védekezés: a session_regenerate_id() 
 * függvény minden kritikus művelet meghívása előtt egy 
 * új session azonosítóra cseréli a régit, 
 * így a támadó már nem fog tudni visszaélni az általa adott ID-vel.
 */

 /**
  * Az űrlapok megjelenítése előtt javasolt egy random string-et 
  *(token) generálni, elmenteni a session-be, majd ugyanezt a string-et 
  *a form egy rejtett mezőjébe is beilleszteni. 
  *A form elküldése után a session-ban lévő token-t összehasonlítva a 
  *rejtett mező tartalmával azonosíthatjuk biztonságosan az oldalt.
  */


/**
 * Note that the salt here is randomly generated.
 * Never use a static salt or one that is not randomly generated.
 *
 * For the VAST majority of use-cases, let password_hash generate the salt randomly for you
 */
$options = [
    'cost' => 11,
    'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
];
echo password_hash("rasmuslerdorf", PASSWORD_BCRYPT, $options);

// See the password_hash() example to see where this came from.
$hash = '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq';

if (password_verify('rasmuslerdorf', $hash)) {
    echo 'Password is valid!';
} else {
    echo 'Invalid password.';
}

?>