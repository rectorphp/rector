<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertIsReadable('...', 'second argument');
        $this->assertArrayHasKey('...', ['...'], 'second argument');
        $this->assertEmpty('...', 'second argument');
        $this->assertFileExists('...', 'second argument');
        $this->assertDirectoryExists('...', 'second argument');
        $this->assertInfinite('...', 'second argument');
        $this->assertNull('...', 'second argument');
        $this->assertIsWritable('...', 'second argument');
        $this->assertNan('...', 'second argument');
    }
}
