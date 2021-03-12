<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\Fixture;

final class SkipFalse
{
    public function run()
    {
        $changedContent = $this->someMethod();
        $this->someMethod();

    }
}
