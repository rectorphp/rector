<?php declare (strict_types=1);

namespace Rector\Tests\RectorOrder;

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertTrue(strpos('foo', 'f') !== false);
        $this->assertNotSame(false, strpos('foo', 'f'));

        $this->assertTrue(strpos($headers['Authorization'][0], 'oauth_body_hash') !== false);
    }
}
