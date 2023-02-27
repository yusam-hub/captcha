<?php

namespace YusamHub\Captcha;

abstract class Captcha
{
    const CAPTCHA_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';
    const CAPTCHA_ALLOW_DIGITS = '0123456789';
    const CAPTCHA_ALLOW_SYMBOLS = '23456789abcdeghkmnpqsuvxyz';

    protected string $alphabet = self::CAPTCHA_ALPHABET;

    protected string $allowedSymbols = self::CAPTCHA_ALLOW_SYMBOLS;

    private string $fontsDir = 'Fonts';

    protected int $length = 6;

    protected int $width = 90;

    protected int $height = 50;

    protected int $fluctuationAmplitude = 0;

    protected bool $noSpaces = false;

    protected bool $showCredits = false;

    protected string $credits = 'credits';

    protected array $backgroundColor = [255, 255, 255];

    protected array $foregroundColor = [];

    /**
     * @return false|string
     * @throws \Exception
     */
    public function makePngContent()
    {
        $this->foregroundColor = [mt_rand(0,100), mt_rand(0,100), mt_rand(0,100)];

        $fonts = [];

        $fontsDir_absolute = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->fontsDir;

        if ($handle = opendir($fontsDir_absolute)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match('/\.png$/i', $file)) {
                    $fonts[] = $fontsDir_absolute . DIRECTORY_SEPARATOR . $file;
                }
            }
            closedir($handle);
        }

        $alphabet_length = strlen($this->alphabet);

        do{
            while(true){
                $captchaValue = '';
                for($i = 0; $i < $this->length; $i++){
                    $captchaValue .= $this->allowedSymbols[random_int(0, strlen($this->allowedSymbols)-1)];
                }
                if(!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $captchaValue)) break;
            }

            $fontFile = $fonts[mt_rand(0, count($fonts) - 1)];
            $font = imagecreatefrompng($fontFile);
            imagealphablending($font, true);
            $fontFile_width = imagesx($font);
            $fontFile_height = imagesy($font)-1;
            $font_metrics = [];
            $symbol = 0;
            $reading_symbol = false;

            for($i = 0; $i < $fontFile_width && $symbol < $alphabet_length; $i++){
                $transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

                if(!$reading_symbol && !$transparent){
                    $font_metrics[$this->alphabet[$symbol]] = ['start' => $i];
                    $reading_symbol = true;
                    continue;
                }

                if($reading_symbol && $transparent){
                    $font_metrics[$this->alphabet[$symbol]]['end'] = $i;
                    $reading_symbol = false;
                    $symbol++;
                    continue;
                }
            }

            $img=imagecreatetruecolor($this->width, $this->height);
            imagealphablending($img, true);
            $white = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, 0, 0, $this->width-1, $this->height-1, $white);

            // draw text
            $x = 1;
            for($i = 0; $i < $this->length; $i++)
            {
                $m = $font_metrics[$captchaValue[$i]];
                $y = mt_rand(-$this->fluctuationAmplitude, $this->fluctuationAmplitude) + ($this->height - $fontFile_height)/2+2;

                if($this->noSpaces)
                {
                    $shift = 0;
                    if ($i > 0)
                    {
                        $shift = 10000;
                        for($sy = 7; $sy < $fontFile_height - 20; $sy += 1){
                            for($sx = $m['start']-1; $sx < $m['end']; $sx += 1){
                                $rgb = imagecolorat($font, $sx, $sy);
                                $opacity = $rgb>>24;
                                if ($opacity < 127)
                                {
                                    $left = $sx - $m['start'] + $x;
                                    $py = $sy + $y;
                                    if ($py > $this->height) break;
                                    for($px = min($left,$this->width-1); $px > $left-12 && $px >= 0; $px -= 1)
                                    {
                                        $color = imagecolorat($img, $px, $py) & 0xff;
                                        if ($color + $opacity < 190)
                                        {
                                            if ($shift > $left-$px)
                                            {
                                                $shift = $left-$px;
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if ($shift == 10000){
                            $shift = mt_rand(4,6);
                        }

                    }
                } else {
                    $shift = 1;
                }
                imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontFile_height);
                $x += $m['end'] - $m['start'] - $shift;
            }
        } while($x >= $this->width - 10);

        $center = $x/2;

        /**
         * CREDITS
         */
        $img2 = imagecreatetruecolor($this->width, $this->height + ($this->showCredits ? 12 : 0));

        $foreground = imagecolorallocate($img2, $this->foregroundColor[0], $this->foregroundColor[1], $this->foregroundColor[2]);
        $background = imagecolorallocate($img2, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);

        imagefilledrectangle($img2, 0, 0, $this->width-1, $this->height-1, $background);
        imagefilledrectangle($img2, 0, $this->height, $this->width - 1, $this->height + 12, $foreground);

        $credits = empty($this->credits) ? $_SERVER['HTTP_HOST'] : $this->credits;

        imagestring($img2, 2, $this->width/2-imagefontwidth(2)*strlen($credits)/2, $this->height-2, $credits, $background);

        // periods
        $rand1 = mt_rand(750000,1200000)/10000000;
        $rand2 = mt_rand(750000,1200000)/10000000;
        $rand3 = mt_rand(750000,1200000)/10000000;
        $rand4 = mt_rand(750000,1200000)/10000000;
        // phases
        $rand5 = mt_rand(0,31415926)/10000000;
        $rand6 = mt_rand(0,31415926)/10000000;
        $rand7 = mt_rand(0,31415926)/10000000;
        $rand8 = mt_rand(0,31415926)/10000000;
        // amplitudes
        $rand9 = mt_rand(330,420)/110;
        $rand10 = mt_rand(330,450)/110;

        //wave distortion

        for($x=0; $x < $this->width; $x++)
        {
            for($y=0; $y < $this->height; $y++)
            {
                $sx = $x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$this->width/2+$center+1;
                $sy = $y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

                if ($sx<0 || $sy<0 || $sx>=$this->width-1 || $sy>=$this->height-1) {
                    continue;
                } else {
                    $color = imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x = imagecolorat($img, $sx+1, $sy) & 0xFF;
                    $color_y = imagecolorat($img, $sx, $sy+1) & 0xFF;
                    $color_xy = imagecolorat($img, $sx+1, $sy+1) & 0xFF;
                }

                if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255) {
                    continue;
                } else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0) {
                    $newred = $this->foregroundColor[0];
                    $newgreen = $this->foregroundColor[1];
                    $newblue = $this->foregroundColor[2];
                } else {
                    $frsx = $sx-floor($sx);
                    $frsy = $sy-floor($sy);
                    $frsx1 = 1-$frsx;
                    $frsy1 = 1-$frsy;

                    $newcolor=(
                        $color * $frsx1 * $frsy1 +
                        $color_x * $frsx * $frsy1 +
                        $color_y * $frsx1 * $frsy +
                        $color_xy * $frsx * $frsy);

                    if ($newcolor > 255) $newcolor = 255;
                    $newcolor = $newcolor/255;
                    $newcolor0 = 1-$newcolor;

                    $newred = $newcolor0 * $this->foregroundColor[0] + $newcolor * $this->backgroundColor[0];
                    $newgreen = $newcolor0 * $this->foregroundColor[1] + $newcolor * $this->backgroundColor[1];
                    $newblue = $newcolor0 * $this->foregroundColor[2] + $newcolor * $this->backgroundColor[2];
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
            }
        }

        $this->setLastImageValue($captchaValue);

        ob_start();
        imagepng($img2);
        $buffer = ob_get_contents();
        ob_end_clean();
        imagedestroy($img2);

        return $buffer;
    }

    /**
     * @param string $value
     * @return void
     */
    abstract protected function setLastImageValue(string $value): void;

    /**
     * @return string
     */
    abstract public function getLastImageValue(): string;

    /**
     * @return string
     */
    public function getAlphabet(): string
    {
        return $this->alphabet;
    }

    /**
     * @param string $alphabet
     */
    public function setAlphabet(string $alphabet): void
    {
        $this->alphabet = $alphabet;
    }

    /**
     * @return string
     */
    public function getAllowedSymbols(): string
    {
        return $this->allowedSymbols;
    }

    /**
     * @param string $allowedSymbols
     */
    public function setAllowedSymbols(string $allowedSymbols): void
    {
        $this->allowedSymbols = $allowedSymbols;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getFluctuationAmplitude(): int
    {
        return $this->fluctuationAmplitude;
    }

    /**
     * @param int $fluctuationAmplitude
     */
    public function setFluctuationAmplitude(int $fluctuationAmplitude): void
    {
        $this->fluctuationAmplitude = $fluctuationAmplitude;
    }

    /**
     * @return bool
     */
    public function isNoSpaces(): bool
    {
        return $this->noSpaces;
    }

    /**
     * @param bool $noSpaces
     */
    public function setNoSpaces(bool $noSpaces): void
    {
        $this->noSpaces = $noSpaces;
    }

    /**
     * @return bool
     */
    public function isShowCredits(): bool
    {
        return $this->showCredits;
    }

    /**
     * @param bool $showCredits
     */
    public function setShowCredits(bool $showCredits): void
    {
        $this->showCredits = $showCredits;
    }

    /**
     * @return string
     */
    public function getCredits(): string
    {
        return $this->credits;
    }

    /**
     * @param string $credits
     */
    public function setCredits(string $credits): void
    {
        $this->credits = $credits;
    }

    /**
     * @return array
     */
    public function getBackgroundColor(): array
    {
        return $this->backgroundColor;
    }

    /**
     * @param array $backgroundColor
     */
    public function setBackgroundColor(array $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @return array
     */
    public function getForegroundColor(): array
    {
        return $this->foregroundColor;
    }

    /**
     * @param array $foregroundColor
     */
    public function setForegroundColor(array $foregroundColor): void
    {
        $this->foregroundColor = $foregroundColor;
    }


}
