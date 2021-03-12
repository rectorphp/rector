<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Keep
{
    public function run(SmartFileInfo $smartFileInfo)
    {
        // no changes
        $originalContent = $smartFileInfo->getContents();
        $expectedContent = $originalContent;

        return $expectedContent;
    }
}
