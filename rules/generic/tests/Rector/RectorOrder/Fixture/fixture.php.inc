<?php

namespace Rector\Generic\Tests\Rector\RectorOrder\Fixture;

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

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\RectorOrder\Fixture;

use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertContains('f', 'foo');
        $this->assertContains('f', 'foo');

        $this->assertContains('oauth_body_hash', $headers['Authorization'][0]);
    }
}

?>
