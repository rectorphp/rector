<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\Fixture;

final class SkipFalse
{
    public function run()
    {
        $changedContent = $this->someMethod();
        $this->someMethod();

    }
}
