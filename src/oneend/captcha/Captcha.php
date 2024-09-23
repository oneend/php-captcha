<?php

namespace oneend\captcha;

class Captcha
{
    const TYPE_STRING = 'string';
    const TYPE_MATHS = 'maths';
    public $type = self::TYPE_STRING;
    public $image;
    public $width = 180;
    public $height = 60;
    public $fontSize = 34;
    public $fonts = [
        __DIR__ . '/fonts/1.ttf',
        __DIR__ . '/fonts/2.ttf',
        __DIR__ . '/fonts/3.ttf',
    ];
    public $key;
    public $content;
    public $result;
    public $setFunc = null;
    public $getFunc = null;
    public $useSession = false;

    public static function create($config = [])
    {
        $self = new self();
        foreach ($config as $name => $value) {
            if (property_exists($self, $name)) {
                $self->$name = $value;
                if ($name == 'useSession' && $value) {
                    $self->startSession();
                }
            }
        }
        return $self;
    }

    public function useMaths($useMaths = true)
    {
        if ($useMaths) {
            $this->type = self::TYPE_MATHS;
        }
        return $this;
    }

    public function get()
    {
        if ($this->useSession) {
            return isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : null;
        }
        if ($this->getFunc && is_callable($this->getFunc)) {
            return call_user_func($this->getFunc, $this->key);
        }
        return null;
    }

    public function set($value)
    {
        if ($this->useSession) {
            $_SESSION[$this->key] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            return true;
        }
        if ($this->setFunc && is_callable($this->setFunc)) {
            return call_user_func($this->setFunc, $this->key, $value);
        }
        return false;
    }

    public function getResult()
    {
        $value = $this->get();
        if (!$value) {
            return false;
        }
        $valueArr = json_decode($value, 1);
        if (!$valueArr || !is_array($valueArr) || !$valueArr['result']) {
            return false;
        }
        return $valueArr['result'];
    }

    public function verify($answer, $result = null)
    {
        if (!is_null($result)) {
            return $this->verifyResult($answer, $result);
        }
        return $this->verifyResult($answer, $this->getResult());
    }

    public function verifyResult($answer, $result)
    {
        return strtolower($answer) == strtolower($result);
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

    public function height($height)
    {
        $this->height = $height;
        return $height;
    }

    public function build()
    {
        if ($this->type == self::TYPE_STRING) {
            $this->content == '' && $this->content = $this->generateContent();
            if (!is_string($this->content) || strlen($this->content) > 10) {
                throw new \Exception('content format error!');
            }
        } elseif ($this->type == self::TYPE_MATHS) {
            if (!is_array($this->content) || !in_array($this->content[1], ['+', '-']) || !is_int($this->content[0]) || !is_int($this->content[2])) {
                throw new \Exception('content format error!');
            }
            $this->content = $this->content[0] . $this->content[1] . $this->content[2];
            $this->result = $this->content[1] === '+' ? ((int)$this->content[0] + (int)$this->content[2]) : ((int)$this->content[0] - (int)$this->content[2]);
        } else {
            throw new \Exception('type error!');
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

        $this->key == null && $this->setKey(md5(uniqid(mt_rand(0, 10), true)));

        $setRes = $this->set(json_encode([
            'key' => $this->key,
            'type' => $this->type,
            'content' => $this->content,
            'result' => $this->type == self::TYPE_STRING ? $this->content : $this->result,
        ]));

        if(!$setRes) {
            throw new \Exception('set failed!');
        }


        return $this;
    }

    public function useSession($useSession = true)
    {
        $this->useSession = $useSession;
        $this->startSession();
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function showImage()
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

    public function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

}
