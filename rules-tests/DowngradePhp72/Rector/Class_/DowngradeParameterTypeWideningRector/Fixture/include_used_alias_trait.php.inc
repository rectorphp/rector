<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

final class SomeContainer implements AnyContainerInterface
{
    use ServiceLocatorTrait;
}

trait ServiceLocatorTrait
{
    public function has(string $name)
    {
    }
}

interface AnyContainerInterface
{
    public function has(string $name);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

final class SomeContainer implements AnyContainerInterface
{
    use ServiceLocatorTrait;
}

trait ServiceLocatorTrait
{
    /**
     * @param string $name
     */
    public function has($name)
    {
    }
}

interface AnyContainerInterface
{
    /**
     * @param string $name
     */
    public function has($name);
}

?>
