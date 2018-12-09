<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');
        $this->assertContains('foo', ['foo', 'bar']);
        $this->assertNotContains('foo', ['foo', 'bar']);

        self::assertContains('foo', 'foo bar');
        self::assertNotContains('foo', 'foo bar');
        self::assertContains('foo', ['foo', 'bar']);
        self::assertNotContains('foo', ['foo', 'bar']);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertStringContains('foo', 'foo bar');
        $this->assertStringNotContains('foo', 'foo bar');
        $this->assertIterableContains('foo', ['foo', 'bar']);
        $this->assertIterableNotContains('foo', ['foo', 'bar']);

        self::assertStringContains('foo', 'foo bar');
        self::assertStringNotContains('foo', 'foo bar');
        self::assertIterableContains('foo', ['foo', 'bar']);
        self::assertIterableNotContains('foo', ['foo', 'bar']);
    }
}

?>
