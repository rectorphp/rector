<?php declare(strict_types=1);

abstract class Testing extends \PHPUnit\Framework\TestCase
{
}

final class MyTest extends Testing
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
        $this->assertNotInstanceOf(Foo::class, $anything, 'argument');
    }
}
