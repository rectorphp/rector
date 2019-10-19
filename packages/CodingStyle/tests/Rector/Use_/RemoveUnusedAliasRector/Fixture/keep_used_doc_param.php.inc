<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use SplFileInfo as NativeSplFileInfo;
use Symfony\Component\Finder\SplFileInfo;

final class KeepUsedDocParam
{
    /**
     * @param SplFileInfo $type
     */
    public function run($type): void
    {
        $splFileInfo = new NativeSplFileInfo('tests/Posts/Year2018/Exceptions/Source/some_file.txt');
    }
}
