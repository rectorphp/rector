<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

final class SkipAbsolutePaths
{
    public function run()
    {
        require '/vendor/autoload.php';
    }
}
