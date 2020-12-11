<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class SelfA
{
    public function covariantReturnTypes(): self
    { /* … */ }
}

class SelfB extends SelfA
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\SelfB
     */
    public function covariantReturnTypes(): self
    { /* … */ }
}

?>
