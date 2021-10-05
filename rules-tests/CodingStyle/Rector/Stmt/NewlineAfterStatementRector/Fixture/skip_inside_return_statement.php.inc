<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class SomeClass
{
}

final class SomeOtherClassClass
{
    public function getSomeClass(): SomeClass
    {
        return new class('foobar') extends SomeClass {
        };
    }
}
