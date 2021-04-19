<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class SkipPublicMethod
{
    public function run(int $notUsed)
    {
    }
}
