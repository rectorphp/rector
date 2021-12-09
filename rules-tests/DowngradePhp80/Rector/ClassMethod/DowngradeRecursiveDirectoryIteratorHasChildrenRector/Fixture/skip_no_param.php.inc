<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\Fixture;

class SkipNoParam
{
    public function hasChildren(): bool
    {
        return true;
    }
}
