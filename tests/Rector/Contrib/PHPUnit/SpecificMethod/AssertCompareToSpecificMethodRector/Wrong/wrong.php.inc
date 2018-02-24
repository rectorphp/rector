<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something));
        $this->assertNotEquals($count, sizeof($something), 'third argument');
        $this->assertEquals('string', gettype($something));
        $this->assertEquals('string', $something['property']());
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}