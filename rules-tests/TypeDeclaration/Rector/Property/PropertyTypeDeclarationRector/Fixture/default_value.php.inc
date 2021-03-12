<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class DefaultValue
{
    private $value = 1.52;
    private $arrayOfStrings = ['a string'];
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class DefaultValue
{
    /**
     * @var float
     */
    private $value = 1.52;
    /**
     * @var string[]
     */
    private $arrayOfStrings = ['a string'];
}

?>
