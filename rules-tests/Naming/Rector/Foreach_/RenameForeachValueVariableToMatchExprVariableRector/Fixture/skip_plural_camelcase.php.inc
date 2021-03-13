<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipPluralCamelcase
{
    public function run(array $filesInDirectory)
    {
        foreach ($filesInDirectory as $fileInDirectory) {
            echo $fileInDirectory;
        }
    }
}

?>
