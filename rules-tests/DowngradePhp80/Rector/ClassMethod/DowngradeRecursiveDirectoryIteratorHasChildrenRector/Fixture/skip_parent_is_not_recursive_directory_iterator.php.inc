<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\Fixture;

class SkipParentIsNotRecursiveDirectoryIterator extends \DateTime
{
    public function hasChildren(bool $allowLinks = false): bool
    {
        return true;
    }
}
