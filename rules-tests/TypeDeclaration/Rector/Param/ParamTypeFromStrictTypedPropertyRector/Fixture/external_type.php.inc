<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Source\ExternalClass;

final class ExternalType
{
    public function setValues($age, ExternalClass $externalClass)
    {
        $externalClass->age = $age;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Source\ExternalClass;

final class ExternalType
{
    public function setValues(int $age, ExternalClass $externalClass)
    {
        $externalClass->age = $age;
    }
}

?>
