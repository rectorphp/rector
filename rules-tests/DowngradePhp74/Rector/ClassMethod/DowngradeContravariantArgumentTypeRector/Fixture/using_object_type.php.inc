<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingObjectType {}

class UsingObjectTypeA
{
    public function contraVariantArguments(UsingObjectType $type)
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

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class UsingObjectType {}

class UsingObjectTypeA
{
    public function contraVariantArguments(UsingObjectType $type)
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
