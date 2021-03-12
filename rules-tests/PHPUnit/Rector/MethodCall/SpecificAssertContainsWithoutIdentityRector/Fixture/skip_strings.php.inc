<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipStrings extends TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar', 'message', false, false);
        $this->assertNotContains('foo', 'foo bar', 'message', false, false);
    }
}
