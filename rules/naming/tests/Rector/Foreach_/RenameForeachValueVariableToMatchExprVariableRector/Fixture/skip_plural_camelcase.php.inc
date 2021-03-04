<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipPluralCamelCase
{
    public function run(array $filesInfosInDirectory)
    {
        foreach ($filesInfosInDirectory as $fileInfosInDirectory) {
            echo $fileInfosInDirectory;
        }
    }
}

?>
