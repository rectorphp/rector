<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

/**
 * @see https://github.com/codeigniter4/CodeIgniter4/blob/2c3f9897762fba3c18f95237686c1297822cdff4/tests/system/Database/Live/FabricatorLiveTest.php#L68-L72
 */
class SkipNonNativeFunction
{
    function myMethod()
    {
        $count = Fabricator::getCount('user');
        fake(UserModel::class, ['country' => 'Italy']);

        $count !== Fabricator::getCount('user');
    }
}

?>
