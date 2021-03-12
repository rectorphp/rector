<?php
namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class SkipInvoke
{
    public function __invoke()
    {
    }
}

(new SkipInvoke())();
