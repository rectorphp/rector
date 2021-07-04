<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SecondPositionInterface
{
    public function has(string $id, callable $callable);
}

final class SomeSecondPosition implements SecondPositionInterface
{
    public function has(string $id, $callable)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SecondPositionInterface
{
    /**
     * @param callable $callable
     */
    public function has(string $id, $callable);
}

final class SomeSecondPosition implements SecondPositionInterface
{
    public function has(string $id, $callable)
    {
    }
}

?>
