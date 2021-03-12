<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class StringOrNull extends TestCase
{
    public function test()
    {
        $this->assertContains('foo', $this->getStringOrNull());
    }

    public function getStringOrNull(): ?string
    {
        if (mt_rand(0, 1)) {
            return 'string';
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class StringOrNull extends TestCase
{
    public function test()
    {
        $this->assertStringContainsString('foo', $this->getStringOrNull());
    }

    public function getStringOrNull(): ?string
    {
        if (mt_rand(0, 1)) {
            return 'string';
        }

        return null;
    }
}

?>
