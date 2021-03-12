<?php
namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class SkipConstructor
{
    public function __construct()
    {
    }
}

new SkipConstructor();
?>
