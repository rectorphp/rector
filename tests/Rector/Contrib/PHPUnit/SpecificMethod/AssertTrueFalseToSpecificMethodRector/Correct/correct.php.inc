<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertArrayHasKey('...', ['...'], 'argument');
        $this->assertContains('...', ['...'], 'argument');
        $this->assertNotIsReadable('...');
        $this->assertEmpty('...');
        $this->assertFileNotExists('...');
        $this->assertDirectoryExists('...');
        $this->assertFinite('...');
        $this->assertNan('...');
        $this->assertNotNull('...');
        $this->assertIsWritable('...');
        $this->assertNotContains('...', ['...'], 'argument');
        $this->assertInstanceOf('Foo', $anything, 'argument');
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}