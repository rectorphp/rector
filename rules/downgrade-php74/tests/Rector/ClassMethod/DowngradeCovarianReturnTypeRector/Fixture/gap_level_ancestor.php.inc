<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector\Fixture;

class GapLevelParentType {}
class GapLevelChildType extends GapLevelParentType {}

class GapLevelA
{
    public function covariantReturnTypes(): GapLevelParentType
    { /* … */ }
}

class GapLevelB extends GapLevelA
{
    public function covariantReturnTypes(): GapLevelParentType
    { /* … */ }
}

class GapLevelC extends GapLevelB
{
    public function covariantReturnTypes(): GapLevelChildType
    { /* … */ }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector\Fixture;

class GapLevelParentType {}
class GapLevelChildType extends GapLevelParentType {}

class GapLevelA
{
    public function covariantReturnTypes(): GapLevelParentType
    { /* … */ }
}

class GapLevelB extends GapLevelA
{
    public function covariantReturnTypes(): GapLevelParentType
    { /* … */ }
}

class GapLevelC extends GapLevelB
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector\Fixture\GapLevelChildType
     */
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector\Fixture\GapLevelParentType
    { /* … */ }
}

?>
