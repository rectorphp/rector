<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class SkipNonProphesizeTest extends TestCase
{
    public function testOne(): void
    {
        $that = new stdClass();
        $prophecy = $that->prophesize(\AnInterface::class);
    }
}
