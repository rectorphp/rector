<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class InterableA
{
    public function covariantReturnTypes(): iterable
    { /* … */ }
}

class InterableB extends InterableA
{
    public function covariantReturnTypes(): array
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class InterableA
{
    public function covariantReturnTypes(): iterable
    { /* … */ }
}

class InterableB extends InterableA
{
    /**
     * @return mixed[]
     */
    public function covariantReturnTypes(): iterable
    { /* … */ }
}

?>
