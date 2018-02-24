<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(isset($node->value1));
        $this->assertFalse(isset($node->value2), 'message');
        $this->assertTrue(isset($node['value1']), 'message');
        $this->assertFalse(isset($node['value2']));
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}