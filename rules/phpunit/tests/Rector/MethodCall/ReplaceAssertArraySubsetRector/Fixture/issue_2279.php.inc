<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

use PHPUnit\Framework\TestCase;

class Issue2279 extends TestCase
{
    public function test()
    {
        $this->assertArraySubset([
            static::KEY_RESPONSE_BAD_REQUEST => static::VALUE_RESPONSE_BAD_REQUEST,
        ], $parameters->getPost()->getResponses(), true);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\Fixture;

use PHPUnit\Framework\TestCase;

class Issue2279 extends TestCase
{
    public function test()
    {
        $this->assertArrayHasKey(static::KEY_RESPONSE_BAD_REQUEST, $parameters->getPost()->getResponses());
        $this->assertSame(static::VALUE_RESPONSE_BAD_REQUEST, $parameters->getPost()->getResponses()[static::KEY_RESPONSE_BAD_REQUEST]);
    }
}

?>
