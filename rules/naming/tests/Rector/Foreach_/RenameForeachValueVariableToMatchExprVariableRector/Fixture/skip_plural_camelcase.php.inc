<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

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
