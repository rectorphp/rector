<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelChildType;
use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelParentType;

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

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelChildType;
use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelParentType;

class TwoLevelA
{
    public function covariantReturnTypes(): TwoLevelParentType
    { /* … */ }
}

class TwoLevelB extends TwoLevelA
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelParentType
    { /* … */ }
}

class TwoLevelC extends TwoLevelB
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\TwoLevelParentType
    { /* … */ }
}

?>
