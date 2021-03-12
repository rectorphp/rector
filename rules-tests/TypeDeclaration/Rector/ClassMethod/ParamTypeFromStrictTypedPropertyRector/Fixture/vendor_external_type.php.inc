<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Source\OutOfControlExternalClass;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Source\OutOfControlExternalClass;

final class VendorExternalType
{
    public function setValues(string $age, OutOfControlExternalClass $outOfControlExternalClass)
    {
        $outOfControlExternalClass->name = $age;
    }
}

?>
