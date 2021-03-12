<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class SkipWithIdentityCheck extends TestCase
{
    public function test()
    {
        $objects = [ new stdClass(), new stdClass(), new stdClass() ];
        $this->assertContains(new stdClass(), $objects, 'message', false, true);
        $this->assertNotContains(new stdClass(), $objects, 'message', false, true);
    }
}
