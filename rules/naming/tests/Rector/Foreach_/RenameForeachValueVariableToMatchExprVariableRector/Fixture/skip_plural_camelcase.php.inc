<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipPluralCamelCase
{
    public function run(array $filesInDirectory)
    {
        foreach ($filesInDirectory as $fileInDirectory) {
            echo $fileInDirectory;
        }
    }
}

?>
