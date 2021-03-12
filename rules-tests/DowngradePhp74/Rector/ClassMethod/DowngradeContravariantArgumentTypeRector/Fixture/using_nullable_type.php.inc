<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingNullableTypeParentType {}
class UsingNullableTypeChildType extends UsingNullableTypeParentType {}

class UsingNullableTypeA
{
    public function contraVariantArguments(?UsingNullableTypeChildType $type)
    { /* … */ }
}

class UsingNullableTypeB extends UsingNullableTypeA
{
    public function contraVariantArguments(?UsingNullableTypeParentType $type)
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingNullableTypeParentType {}
class UsingNullableTypeChildType extends UsingNullableTypeParentType {}

class UsingNullableTypeA
{
    public function contraVariantArguments(?UsingNullableTypeChildType $type)
    { /* … */ }
}

class UsingNullableTypeB extends UsingNullableTypeA
{
    /**
     * @param \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture\UsingNullableTypeParentType|null $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
