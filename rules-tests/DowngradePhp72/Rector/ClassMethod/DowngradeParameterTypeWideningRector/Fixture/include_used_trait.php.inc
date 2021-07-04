<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\AnotherContainerInterface;

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

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\AnotherContainerInterface;

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

?>
