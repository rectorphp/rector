<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassProperty
{
    /**
     * @var AnotherClass
     */
    private $anotherClass;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassProperty
{
    private \Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyRector\Source\AnotherClass $anotherClass;
}

?>
