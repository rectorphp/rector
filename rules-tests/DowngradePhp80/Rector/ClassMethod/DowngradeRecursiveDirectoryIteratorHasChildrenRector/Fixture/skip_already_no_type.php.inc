<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\Fixture;

class RecursiveDirectoryIteratorChild extends \RecursiveDirectoryIterator
{
    public function hasChildren($allowLinks = false): bool
    {
        return true;
    }
}
