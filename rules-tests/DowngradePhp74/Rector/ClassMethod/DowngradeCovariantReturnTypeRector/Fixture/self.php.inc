<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class SelfA
{
    public function covariantReturnTypes(): self
    { /* … */ }
}

class SelfB extends SelfA
{
    public function covariantReturnTypes(): SelfB
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class SelfA
{
    public function covariantReturnTypes(): self
    { /* … */ }
}

class SelfB extends SelfA
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SelfA
    { /* … */ }
}

?>
