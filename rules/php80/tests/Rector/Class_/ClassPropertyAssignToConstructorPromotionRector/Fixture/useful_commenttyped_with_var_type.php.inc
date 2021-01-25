<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Source\SomeTypedPropertyClass;

final class UsefulCommentTypeWithVarType
{
    /**
     * @var SomeTypedPropertyClass This is really useful
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

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Source\SomeTypedPropertyClass;

final class UsefulCommentTypeWithVarType
{
    public function __construct(
        /**
         * @var SomeTypedPropertyClass This is really useful
         */
        private SomeTypedPropertyClass $someTypedPropertyClass
    )
    {
    }
}

?>
