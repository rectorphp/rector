<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class PluralExcludedGetNamesClass
{
    public function run()
    {
        $words = [];
        foreach ($this->getPluralNames() as $word) {
            $words[] = $word;
        }

        return $words;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getPluralNames(): array
    {
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class PluralExcludedGetNamesClass
{
    public function run()
    {
        $words = [];
        foreach ($this->getPluralNames() as $pluralName) {
            $words[] = $pluralName;
        }

        return $words;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getPluralNames(): array
    {
    }
}

?>
