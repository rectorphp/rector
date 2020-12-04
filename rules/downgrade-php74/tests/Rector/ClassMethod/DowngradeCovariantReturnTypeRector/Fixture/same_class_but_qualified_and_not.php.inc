<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class SameButDifferentParentType {}
class SameButDifferentChildType extends SameButDifferentParentType {}

class SameButDifferentA
{
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SameButDifferentParentType
    { /* … */ }
}

class SameButDifferentB extends SameButDifferentA
{
    public function covariantReturnTypes(): SameButDifferentParentType
    { /* … */ }
}

class SameButDifferentC extends SameButDifferentB
{
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SameButDifferentParentType
    { /* … */ }
}

?>
