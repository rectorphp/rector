<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotFalse(strpos($node, 'foo'));
        $this->assertFalse(stripos($node, 'foo'), 'message');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}