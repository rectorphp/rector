<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\GapLevelParentType
    { /* … */ }
}

?>
