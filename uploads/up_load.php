<?php
$jsonres = array();
if(!isset($_COOKIE['felhasznalo']) || $_COOKIE['felhasznalo'] == "") {
    $jsonres["status"] = "Kérlek jelentkezz be";
    header("Content-Type","application/json");
    print json_encode($jsonres);
    exit;
}
$target_dir = "kepek/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        $jsonres["status"][] = "A fájl egy kép - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        $jsonres["status"][] = "A megadott fájl nem kép.";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    $jsonres["status"][] = "A fájl már létezik.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    $jsonres["status"][] = "Túl nagy méretű a megadott kép.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    $jsonres["status"][] = "Csak JPG, JPEG, PNG & GIF fájlok megengedettek.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    $jsonres["status"][] = "Hiba a feltöltés során.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $jsonres["status"][] = "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        setcookie("keput", $target_file, 3600, "/", "");
    } else {
        $jsonres["status"][] = "Nem sikerült a fájl feltöltése.";
    }
}

header("Content-Type","application/json");
print json_encode($jsonres);
?>