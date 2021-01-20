<?php
namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

final class SkipConstructor
{
    public function __construct()
    {
    }
}

new SkipConstructor();
?>
