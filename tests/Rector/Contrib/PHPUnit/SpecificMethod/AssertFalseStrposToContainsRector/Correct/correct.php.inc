<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', $node);
        $this->assertNotContains('foo', $node, 'message');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}