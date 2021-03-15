<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Source\SomeTypedPropertyClass;

class TypeWithVarType
{
    /**
     * @var SomeTypedPropertyClass
     */
    private $someTypedPropertyClass;

    public function __construct(SomeTypedPropertyClass $someTypedPropertyClass)
    {
        $this->someTypedPropertyClass = $someTypedPropertyClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Source\SomeTypedPropertyClass;

class TypeWithVarType
{
    public function __construct(private SomeTypedPropertyClass $someTypedPropertyClass)
    {
    }
}

?>
