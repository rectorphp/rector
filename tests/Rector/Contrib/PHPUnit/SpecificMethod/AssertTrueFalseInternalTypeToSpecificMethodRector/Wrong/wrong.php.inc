<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(\is_string($something));
        $this->assertFalse(\is_array($something));

        $this->assertTrue(is_integer($something), 'third argument');
        $this->assertFalse(is_bool($something), 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}