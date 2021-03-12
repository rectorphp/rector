<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class TwoLevelParentType {}
class TwoLevelChildType extends TwoLevelParentType {}

class TwoLevelA
{
    public function covariantReturnTypes(): TwoLevelParentType
    { /* … */ }
}

class TwoLevelB extends TwoLevelA
{
    public function covariantReturnTypes(): TwoLevelChildType
    { /* … */ }
}

class TwoLevelC extends TwoLevelB
{
    public function covariantReturnTypes(): TwoLevelChildType
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class TwoLevelParentType {}
class TwoLevelChildType extends TwoLevelParentType {}

class TwoLevelA
{
    public function covariantReturnTypes(): TwoLevelParentType
    { /* … */ }
}

class TwoLevelB extends TwoLevelA
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\TwoLevelParentType
    { /* … */ }
}

class TwoLevelC extends TwoLevelB
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\TwoLevelParentType
    { /* … */ }
}

?>
