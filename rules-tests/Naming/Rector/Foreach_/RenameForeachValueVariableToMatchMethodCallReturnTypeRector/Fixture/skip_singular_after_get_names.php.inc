<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class SkipSingularAfterGetNamesClass
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
