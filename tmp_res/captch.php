<?php
session_start();
$fontdir = __DIR__."/fonts/";

$fonts = array(
    $fontdir."Achafexp.ttf",
    $fontdir."Achafita.ttf",
    $fontdir."Achaflft.ttf",
    $fontdir."Achafont.ttf",
    $fontdir."Achafout.ttf",
    $fontdir."Achafsex.ttf"
);

$kepSzelesseg = 250;
$kepMagassag = 70;
$code=rand(10000,99999);
$_SESSION["code"]=$code;

$font = $fonts[rand(0, count($fonts) - 1)];

$im = imagecreatetruecolor($kepSzelesseg,$kepMagassag);

//$randcolor = imagecolorallocate(rand(1,255),rand(1,255),rand(1,255));
//$bg = imagecolorallocate($im, 10, 150, 200); //backgrond
$bg = imagecolorallocate($im, 255, 255, 255); //backgrond
//$bg = imagecolorallocatealpha($im, 255, 217, 4, 0.404); //with opacity

$fg = imagecolorallocate($im, 0, 0, 0);//text color white
$fgline = imagecolorallocate($im, 0, 0, 0);
imagefill($im, 0, 0, $bg);
//imagestring($im,5, 5, 5,  $code, $fg);
imagettftext($im, 50, 3, 4, 55, $fg, $font,  $code);

for($i=0; $i<$kepMagassag; $i += 10) {
    $randcolor = imagecolorallocate($im, rand(1,255),rand(1,255),rand(1,255));
    $szelesseg = rand(1,$kepSzelesseg);
    imageline($im, 0, $i, $szelesseg, $i,$randcolor);
    imageline($im, 0, $i+1, $szelesseg, $i+1,$randcolor);
}
for($i=0; $i<$kepSzelesseg; $i += 20) {
    $randcolor = imagecolorallocate($im,rand(1,255),rand(1,255),rand(1,255));
    $hosszba = rand(1,$kepMagassag);
    imageline($im, $i,0, $i, $hosszba,$randcolor);
}



header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/jpeg');
imagepng($im);
imagedestroy($im);
?>