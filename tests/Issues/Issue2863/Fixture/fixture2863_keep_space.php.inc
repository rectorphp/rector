<?php

namespace Rector\Core\Tests\Issues\Issue2863\Fixture;

class Fixture2863KeepSpace
{
    public function bar($bar)
    {
        // @var string $bar['should_not_be_changed]

        return $bar['should_not_be_changed'];
    }
}
