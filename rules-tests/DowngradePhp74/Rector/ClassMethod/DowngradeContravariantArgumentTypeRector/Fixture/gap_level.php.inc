<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class GapLevelParentType {}
class GapLevelChildType extends GapLevelParentType {}

class GapLevelA
{
    public function contraVariantArguments(GapLevelChildType $type)
    { /* … */ }
}

class GapLevelB extends GapLevelA
{
}

class GapLevelC extends GapLevelB
{
    public function contraVariantArguments(GapLevelParentType $type)
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class GapLevelParentType {}
class GapLevelChildType extends GapLevelParentType {}

class GapLevelA
{
    public function contraVariantArguments(GapLevelChildType $type)
    { /* … */ }
}

class GapLevelB extends GapLevelA
{
}

class GapLevelC extends GapLevelB
{
    /**
     * @param \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture\GapLevelParentType $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
