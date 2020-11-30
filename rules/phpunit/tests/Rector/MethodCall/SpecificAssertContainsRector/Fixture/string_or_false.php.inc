<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class StringOrFalse extends TestCase
{
    public function test()
    {
        $this->assertContains('foo', $this->getStringOrFalse());
    }

    /**
     * @return false|string
     */
    public function getStringOrFalse()
    {
        if (mt_rand(0, 1)) {
            return 'string';
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class StringOrFalse extends TestCase
{
    public function test()
    {
        $this->assertStringContainsString('foo', $this->getStringOrFalse());
    }

    /**
     * @return false|string
     */
    public function getStringOrFalse()
    {
        if (mt_rand(0, 1)) {
            return 'string';
        }

        return false;
    }
}

?>
