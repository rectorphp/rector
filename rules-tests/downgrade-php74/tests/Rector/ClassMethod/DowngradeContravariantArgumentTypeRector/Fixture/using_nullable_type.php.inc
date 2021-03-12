<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

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
     * @param \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture\UsingNullableTypeParentType|null $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
