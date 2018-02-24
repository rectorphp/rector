<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedExceptionRegExp('SomeException');
        $this->expectExceptionMessageRegExp('SomREGEPX');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}