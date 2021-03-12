<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use SplFileInfo as NativeSplFileInfo;
use Symfony\Component\Finder\SplFileInfo;

final class KeepUsedSplFileInfo
{
    public function testSplFileInfo(): void
    {
        $splFileInfo = new NativeSplFileInfo('tests/Posts/Year2018/Exceptions/Source/some_file.txt');

        /** @var SplFileInfo $file */
        $file = array_pop($files);
    }
}
