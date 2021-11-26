<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Source\ParentClassWithProperty;

final class SomeClass extends ParentClassWithProperty
{
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Source\ParentClassWithProperty;

final class SomeClass extends ParentClassWithProperty
{
    public string $name;
}

?>
