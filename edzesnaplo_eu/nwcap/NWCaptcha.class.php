<?php
    class NWCaptcha 
    {
        protected $fontdir = __DIR__."\\fonts\\";
        protected $fonts = NULL;
        protected $font = NULL;

        protected $kepSzelesseg = NULL;
        protected $kepMagassag = NULL;
        protected $kepMeret = NULL;
        protected $im = NULL; //image resource

        protected $hatterSzin = NULL; //stringként jön RGB , "255,255,255"
        protected $zavaroSzin = NULL;
        protected $betuSzin = NULL;
        protected $arnyekSzin = NULL;

        protected $textAngel = 1;
        protected $torzitas = NULL;
        protected $hatulTorzit = NULL;

        protected $code = NULL;
        protected $codeLenght = NULL;

        function __construct($width, $height, 
            $hatterszin = "255,255,255", 
            $zavaroszin = "0,0,0", 
            $betuszin = "255,255,255",
            $arnyekszin = "0,0,0",
            $kepmeret = 25,
            $kodhossz = 7,
            $torzitas = 10,
            $hatulTorzit = 6)
        {
            
            $this->kepSzelesseg = $width;
            $this->kepMagassag = $height;
            $this->kepMeret = $kepmeret;
            $this->torzitas = $torzitas;
            $this->hatulTorzit = $hatulTorzit;
            $this->codeLenght = $kodhossz;

            $this->initFonts();
            $this->setCode();

            $this->initImage($this->kepSzelesseg, $this->kepMagassag);
            $this->hatterSzin = $this->getSzin($hatterszin);
            $this->zavaroSzin = $this->getSzin($zavaroszin);
            $this->betuSzin = $this->getSzin($betuszin);
            $this->arnyekSzin = $this->getSzin($arnyekszin);
        }

        private function initImage($width, $height)
        {
            $this->im = imagecreatetruecolor($width, $height);
        }

        private function initFonts() 
        {
            $this->loadFonts($this->fontdir);
        }

        private function loadFonts($dir) {
            $adir = opendir($dir) or die("Nem tudtam megnyitni a font könyvtárat");
            while($file = readdir($adir)) {
                if(!is_dir($file)) {
                    $exp = explode(".", $file);
                    if(is_array($exp) && count($exp) > 1) {
                        $this->fonts[] = $dir.$exp[0].".ttf";
                    }
                }
                
            }
            closedir($adir);
        }

        private function getSzin($szin) 
        {
            $szettort = explode(",",$szin);
            $r = $szettort[0];
            $g = $szettort[1];
            $b = $szettort[2];

            return imagecolorallocate($this->im, $r, $g, $b);
        }

        protected function setCode() {
            $elso = "1";
            $masodik = "9";

            for($i = 0; $i< $this->codeLenght - 1; $i++) {
                $elso .= "0";
                $masodik .= "9";
            }

            $this->code = rand((integer) $elso, (integer) $masodik);
        }

        public function kepKirak() {
            //$this->font = $this->fonts[$this->rit()];
            imagefill($this->im, 0, 0, $this->hatterSzin);
            
            $this->hatulsoKepZavaras();
            //egy példából merítve
            for($i=0; $i<$this->codeLenght; $i++) {
                $clockwise = rand(1,2);
                $textrot = 0;
                if($clockwise == 1) {
                    $textrot = rand(0,25);
                }
                if($clockwise == 2) {
                    $textrot = rand(345, 360);
                }

                $fontfile = $this->fonts[$this->rit(4)];
                imagettftext($this->im,rand(25,$this->kepMeret), 
                            $textrot, ($i*30), 40, 
                            $this->betuSzin, 
                            $fontfile, 
                            substr($this->code, ($i), 1)) or die("hiba: ".$fontfile);
            }
            
            $this->kepZavaras();

            header("Cache-Control: no-cache, must-revalidate");
            header('Content-type: image/jpeg');
            imagepng($this->im);
            imagedestroy($this->im);
            //unset($this->code);
        }

        /**
         * Random számot generál a fonts tömb méretével
         * Ha adunk egy paramétert, akkor annak megfelelően generálja a random számot
         */
        
        protected function rit() {
            if(func_num_args() > 0 && is_numeric(func_get_arg(0))) {
                return rand(0, func_get_arg(0));
            } else {
                return rand(0, count($this->fonts)-1);
            }
        }

        protected function kepZavaras() {
            for($i=0; $i<$this->torzitas; $i++) {
                $randcolor = imagecolorallocate($this->im, rand(1,255),rand(1,255),rand(1,255));
                //$color = $this->zavaroSzin;
                $szelesseg = rand(1,$this->kepSzelesseg);
                $x1 = mt_rand(1,$this->kepSzelesseg);
				$x2 = mt_rand(1,$this->kepSzelesseg);
				$y1 = mt_rand(1,$this->kepMagassag);
				$y2 = mt_rand(1,$this->kepMagassag);
                imageline($this->im, $x1, $x2, $y2, $y2,$randcolor);
            }
        }

        protected function hatulsoKepZavaras() {
            for($i = 0; $i< floor($this->torzitas / $this->hatulTorzit); $i++) {
                $randcolor = imagecolorallocate($this->im, rand(1,255),rand(1,255),rand(1,255));
                imagearc($this->im, $this->kepSzelesseg / rand(1,4) ,
                         $this->kepMagassag / rand(1,10), 
                         $i*25,50, 0,360, $randcolor);
            }
        }

        public function testRandom() {
            echo "Generated random number is: ".$this->code."\n";
        }

        public function getCode() {
            return $this->code;
        }

        public function writeFonts() {
            foreach($this->fonts as $font) {
                echo "Használt Fontok: ".$font."<br>";
            }
        }
    }
?>