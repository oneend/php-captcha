<?php
namespace oneend\captcha;

class Captcha
{
    public $image;
    public $width = 200;
    public $height = 60;
    public $fontSize = 34;
    public $fonts = [
        __DIR__ . '/fonts/1.ttf',
        __DIR__ . '/fonts/2.ttf',
        __DIR__ . '/fonts/3.ttf',
    ];

    public $content;

    public static function create($config = [])
    {
        $self = new self();
        foreach ($config as $name => $value) {
            if (property_exists($self, $name)) {
                $self->$name = $value;
            }
        }
        return $self;
    }

    public function getContent($value)
    {
        return $this->content;
    }

    public function setContent($value = null)
    {
        $this->content = $value;
        return $this;
    }

    public function width($width)
    {
        $this->width = $width;
        return $this;
    }

    public function build()
    {

        if ($this->content == null) {
            $this->content = $this->generateContent();
        }

        $this->image = imagecreatetruecolor($this->width, $this->height);

        $backgroundColor = imagecolorallocate($this->image, mt_rand(185, 255), mt_rand(185, 255), mt_rand(185, 255));
        $textColor = imagecolorallocate($this->image, mt_rand(0, 105), mt_rand(0, 155), mt_rand(0, 105));

        imagefill($this->image, 0, 0, $backgroundColor);

        for ($i = 0; $i < 6; $i++) {
            imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), imagecolorallocate($this->image, mt_rand(0, 185), mt_rand(0, 185), mt_rand(0, 185)));
        }

        $codeValueLen = strlen($this->content);
        $maxFontSize = (int)($this->width / ($codeValueLen + 4));
        $maxFontSize < $this->fontSize && $this->fontSize = $maxFontSize;

        $x = (int)(($this->width - $this->fontSize * ($codeValueLen + 2)) / 2);
        $y = (int)(($this->height + $this->fontSize) / 2);

        $codeValueArray = str_split($this->content);
        foreach ($codeValueArray as $one) {
            imagettftext($this->image, mt_rand($this->fontSize - 4, $this->fontSize + 8), mt_rand(-25, 25), mt_rand($x - 2, $x + 2), $y, $textColor, $this->fonts[array_rand($this->fonts)], $one);
            $x += $this->fontSize + mt_rand(-1, 5);
        }

        return $this;
    }

    public function show()
    {
        header('Content-Type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
    }

    public function getBase64()
    {
        ob_start();
        imagepng($this->image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($this->image);
        return base64_encode($imageData);
    }

    public function save($filePath)
    {
        imagepng($this->image, $filePath);
        imagedestroy($this->image);
        return true;
    }

    public function generateContent($length = 4)
    {
        $characters = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

}

Captcha::create()
    ->setContent()
    ->width(800)
    ->build()
    ->show();

