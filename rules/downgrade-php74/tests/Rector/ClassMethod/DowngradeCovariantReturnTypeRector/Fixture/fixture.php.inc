<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    { /* … */ }
}

class B extends A
{
    public function covariantReturnTypes(): ChildType
    { /* … */ }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    { /* … */ }
}

class B extends A
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\ChildType
     */
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\ParentType
    { /* … */ }
}

?>
