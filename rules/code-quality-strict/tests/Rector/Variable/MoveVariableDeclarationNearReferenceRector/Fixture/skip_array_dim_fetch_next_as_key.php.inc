<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
