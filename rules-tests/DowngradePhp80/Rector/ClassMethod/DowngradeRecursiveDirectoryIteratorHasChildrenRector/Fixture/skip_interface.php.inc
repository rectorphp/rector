<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\Fixture;

interface SkipInterface
{
    public function hasChildren(bool $allowLinks = false): bool;
}
