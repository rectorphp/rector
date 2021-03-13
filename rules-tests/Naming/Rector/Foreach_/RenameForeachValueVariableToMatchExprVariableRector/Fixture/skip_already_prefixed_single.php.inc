<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipAlreadyPrefixedSingle
{
    public function run()
    {
        foreach ($singleTypesAsString as $key => $singleTypeAsString) {
            $singleTypesAsString[$key] = $singleTypeAsString . '[]';
        }
    }
}

?>
