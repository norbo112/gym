<?php
session_start();
header("Content-Type: application/json");

$valasz = array();

$valasz["success"] = "Most mindig sikeres";
print json_encode($valasz);
exit;

if (isset($_POST["captcha"]) && $_POST["captcha"] != ""
    && $_SESSION["code"] == $_POST["captcha"]) {
    $valasz["success"] = "Sikeres egyezés";
    unset($_SESSION["code"]);
    print json_encode($valasz);
} else {
    $valasz["failed"] = "failed";
    print json_encode($valasz);
}
?>