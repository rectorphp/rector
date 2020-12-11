<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class WithInterfaceParentType {}
class WithInterfaceChildType extends WithInterfaceParentType {}

interface WithInterfaceA
{
    public function contraVariantArguments(WithInterfaceChildType $type);
}

interface WithInterfaceB extends WithInterfaceA
{
    public function contraVariantArguments(WithInterfaceParentType $type);
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class WithInterfaceParentType {}
class WithInterfaceChildType extends WithInterfaceParentType {}

interface WithInterfaceA
{
    public function contraVariantArguments(WithInterfaceChildType $type);
}

interface WithInterfaceB extends WithInterfaceA
{
    /**
     * @param \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture\WithInterfaceParentType $type
     */
    public function contraVariantArguments($type);
}

?>
