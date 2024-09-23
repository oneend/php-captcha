<?php

use oneend\captcha\Captcha;
use PHPUnit\Framework\TestCase;

class CaptchaTest extends TestCase
{

    public function testCreate()
    {

        // save captcha
        $captcha = Captcha::create();
        $captcha->useSession()->build()->save('captcha.png');
        $this->assertTrue(file_exists(__DIR__ . '/../captcha.png'));

        // get base64
        $captcha = Captcha::create();
        $base64 = $captcha->useSession()->build()->getBase64();
        $this->assertTrue(is_string($base64));

        // maths && use session
        $captcha = Captcha::create();
        $captcha->useSession()->useMaths()->setContent([1, '+', 2])->build()->getBase64();
        $this->assertEquals(3, $captcha->getResult());
        $this->assertTrue($captcha->verify(3));

        // set && get
        $captcha = Captcha::create([
            'setFunc' => function ($key, $value) {
                return file_put_contents(__DIR__ . "/../{$key}", $value);
            },
            'getFunc' => function ($key) {
                return file_get_contents(__DIR__ . "/../{$key}");
            }
        ]);

        $captcha->setContent("abcd")->build()->getBase64();
        $this->assertEquals('abcd', $captcha->getResult());

        $key = $captcha->key;

        // verify
        $captcha = Captcha::create([
            'getFunc' => function ($key) {
                return file_get_contents(__DIR__ . "/../{$key}");
            }
        ])->setKey($key);

        $this->assertEquals('abcd', $captcha->getResult());
        $this->assertFalse($captcha->verify('abcc'));
    }

}