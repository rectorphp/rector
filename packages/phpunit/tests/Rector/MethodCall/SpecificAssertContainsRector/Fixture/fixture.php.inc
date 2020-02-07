<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');

        self::assertContains('foo', 'foo bar');
        self::assertNotContains('foo', 'foo bar');
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
        $this->assertStringContainsString('foo', 'foo bar');
        $this->assertStringNotContainsString('foo', 'foo bar');

        self::assertStringContainsString('foo', 'foo bar');
        self::assertStringNotContainsString('foo', 'foo bar');
    }
}

?>
