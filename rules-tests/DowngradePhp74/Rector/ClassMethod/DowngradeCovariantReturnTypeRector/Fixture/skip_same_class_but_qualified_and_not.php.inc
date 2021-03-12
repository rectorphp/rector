<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class SameButDifferentParentType {}
class SameButDifferentChildType extends SameButDifferentParentType {}

class SameButDifferentA
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SameButDifferentParentType
    { /* … */ }
}

class SameButDifferentB extends SameButDifferentA
{
    public function covariantReturnTypes(): SameButDifferentParentType
    { /* … */ }
}

class SameButDifferentC extends SameButDifferentB
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SameButDifferentParentType
    { /* … */ }
}

?>
