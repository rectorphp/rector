<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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
