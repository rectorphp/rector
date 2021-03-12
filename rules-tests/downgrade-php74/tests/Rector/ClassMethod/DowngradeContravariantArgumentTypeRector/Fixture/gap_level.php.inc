<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

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
     * @param \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture\GapLevelParentType $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}

?>
