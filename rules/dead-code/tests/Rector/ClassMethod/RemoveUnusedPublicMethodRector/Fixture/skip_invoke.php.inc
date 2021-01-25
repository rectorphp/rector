<?php
namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class SkipInvoke
{
    public function __invoke()
    {
    }
}

(new SkipInvoke())();
?>
