<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\Fixture;

final class SkipAlreadyNop
{
    public function run()
    {
        $matches = Strings::matchAll($input, '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!');

        $parts = [];
    }
}
