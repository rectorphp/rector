<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipArrayDimFetchNextAsKey
{
   private function foo($data, $aliasName)
    {
        $loweredAliasName = strtolower($aliasName);

        if (isset($data[$loweredAliasName])) {
            return true;
        }

        echo $loweredAliasName;
    }
}

?>
