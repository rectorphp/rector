<?php declare (strict_types=1);

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(strpos('foo', 'f') !== false);
        $this->assertNotSame(false, strpos('foo', 'f'));

        $this->assertTrue(strpos($headers['Authorization'][0], 'oauth_body_hash') !== false);
    }
}
