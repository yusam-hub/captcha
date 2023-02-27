<?php

namespace YusamHub\Captcha\Tests;

use YusamHub\Captcha\Captcha;

class CaptchaDemo extends Captcha
{
    /**
     * @var CaptchaDemo|null
     */
    private static ?CaptchaDemo $instance = null;

    /**
     * @return CaptchaDemo
     */
    public static function Instance(): CaptchaDemo
    {
        if (is_null(static::$instance)) {
            static::$instance = new CaptchaDemo();
        }
        return static::$instance;
    }

    protected function setLastImageValue(string $value): void
    {
        file_put_contents(__DIR__ ."/../tmp/captcha-demo.data", $value);
    }

    public function getLastImageValue(): string
    {
        return file_get_contents(__DIR__ ."/../tmp/captcha-demo.data");
    }
}
