<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class ExternalType
{
    public function setValues($age, ExternalClass $externalClass)
    {
        $externalClass->age = $age;
    }
}

final class ExternalClass
{
    public int $age;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class ExternalType
{
    public function setValues(int $age, ExternalClass $externalClass)
    {
        $externalClass->age = $age;
    }
}

final class ExternalClass
{
    public int $age;
}

?>
