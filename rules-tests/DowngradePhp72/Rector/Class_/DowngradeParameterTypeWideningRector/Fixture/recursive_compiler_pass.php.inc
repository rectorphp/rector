<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

abstract class AbstractRecursivePass
{
    protected function processValue($value, bool $isRoot = false)
    {
    }
}

final class FirstCompilerPass extends AbstractRecursivePass
{
    protected function processValue($value, $isRoot = false)
    {
    }
}

final class SecondCompilerPass extends AbstractRecursivePass
{
    protected function processValue($value, bool $isRoot = false)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

abstract class AbstractRecursivePass
{
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = false)
    {
    }
}

final class FirstCompilerPass extends AbstractRecursivePass
{
    protected function processValue($value, $isRoot = false)
    {
    }
}

final class SecondCompilerPass extends AbstractRecursivePass
{
    /**
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = false)
    {
    }
}

?>
