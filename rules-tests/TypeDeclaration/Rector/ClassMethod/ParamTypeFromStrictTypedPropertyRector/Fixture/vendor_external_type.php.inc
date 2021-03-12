<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Source\OutOfControlExternalClass;

final class VendorExternalType
{
    public function setValues($age, OutOfControlExternalClass $outOfControlExternalClass)
    {
        $outOfControlExternalClass->name = $age;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Source\OutOfControlExternalClass;

final class VendorExternalType
{
    public function setValues(string $age, OutOfControlExternalClass $outOfControlExternalClass)
    {
        $outOfControlExternalClass->name = $age;
    }
}

?>
