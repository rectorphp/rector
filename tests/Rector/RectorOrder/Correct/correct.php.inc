<?php declare (strict_types=1);

namespace Rector\Tests\RectorOrder;

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
