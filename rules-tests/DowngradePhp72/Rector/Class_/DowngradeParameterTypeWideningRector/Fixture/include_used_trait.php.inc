<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

final class YetAnotherContainer implements AnotherContainerInterface
{
    use AnotherServiceLocatorTrait;
}

trait AnotherServiceLocatorTrait
{
    public function get(string $name)
    {
    }
}

interface AnotherContainerInterface
{
    public function get($name);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

final class YetAnotherContainer implements AnotherContainerInterface
{
    use AnotherServiceLocatorTrait;
}

trait AnotherServiceLocatorTrait
{
    /**
     * @param string $name
     */
    public function get($name)
    {
    }
}

interface AnotherContainerInterface
{
    public function get($name);
}

?>
