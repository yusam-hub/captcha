<?php

namespace YusamHub\Captcha\Tests;

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $content = CaptchaDemo::Instance()->makePngContent();
        file_put_contents(__DIR__ ."/../tmp/captcha-demo.png", $content);
        $storedLastImageValue = file_get_contents(__DIR__ ."/../tmp/captcha-demo.data");
        $this->assertTrue($storedLastImageValue == CaptchaDemo::Instance()->getLastImageValue());
    }
}
