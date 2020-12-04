<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingNullableObjectType {}

class UsingNullableObjectTypeA
{
    public function contraVariantArguments(?UsingNullableObjectType $type)
    { /* … */ }
}

class UsingNullableObjectTypeB extends UsingNullableObjectTypeA
{
    public function contraVariantArguments(?object $type)
    { /* … */ }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingNullableObjectType {}

class UsingNullableObjectTypeA
{
    public function contraVariantArguments(?UsingNullableObjectType $type)
    { /* … */ }
}

class UsingNullableObjectTypeB extends UsingNullableObjectTypeA
{
    /**
     * @param object|null $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
