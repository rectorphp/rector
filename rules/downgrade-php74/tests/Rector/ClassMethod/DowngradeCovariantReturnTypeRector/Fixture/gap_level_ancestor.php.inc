<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\GapLevelChildType
     */
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\GapLevelParentType
    { /* … */ }
}

?>
