<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\Fixture;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class SkipIfAlreadyAdded extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
