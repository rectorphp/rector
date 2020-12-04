<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingObjectTypeA
{
    public function contraVariantArguments(ChildType $type)
    { /* … */ }
}

class UsingObjectTypeB extends UsingObjectTypeA
{
    public function contraVariantArguments(object $type)
    { /* … */ }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingObjectTypeA
{
    public function contraVariantArguments(ChildType $type)
    { /* … */ }
}

class UsingObjectTypeB extends UsingObjectTypeA
{
    /**
     * @param object $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
