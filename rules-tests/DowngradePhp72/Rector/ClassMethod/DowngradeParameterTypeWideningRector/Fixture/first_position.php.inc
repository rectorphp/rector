<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface FirstPositionInterface
{
    public function has(callable $callable);
}

final class SomeFirstPosition implements FirstPositionInterface
{
    public function has($callable)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface FirstPositionInterface
{
    /**
     * @param callable $callable
     */
    public function has($callable);
}

final class SomeFirstPosition implements FirstPositionInterface
{
    public function has($callable)
    {
    }
}

?>
