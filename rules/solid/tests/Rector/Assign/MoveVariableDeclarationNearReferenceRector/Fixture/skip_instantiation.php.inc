<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class SkipInstantiation
{
    /**
     * @param SmartFileInfo[] $foundFiles
     */
    function myMethod(array $foundFiles)
    {
        $foundFileNames = [];
        foreach ($foundFiles as $foundFile) {
            $foundFileNames[] = $foundFile->getFilename();
        }
    }
}
