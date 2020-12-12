<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class SkipSingularAfterGetNames
{
    public function run()
    {
        $words = [];
        foreach ($this->getSingularName() as $word) {
            $words[] = $word;
        }

        return $words;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getSingularName(): array
    {
    }
}

?>
