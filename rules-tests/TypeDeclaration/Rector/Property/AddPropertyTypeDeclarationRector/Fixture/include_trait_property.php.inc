<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Source\SomeTraitWithProperty;

final class IncludeTraitProperty
{
    use SomeTraitWithProperty;

    public $value;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector\Source\SomeTraitWithProperty;

final class IncludeTraitProperty
{
    use SomeTraitWithProperty;

    public int $value;
}

?>
