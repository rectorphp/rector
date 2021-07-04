<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeContainerInterface
{
    public function has(string $id);
}

final class SomeContainerBuilder extends SomeUniqueContainer
{
    public function has($id)
    {
    }
}

class SomeUniqueContainer implements SomeContainerInterface
{
    public function has($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeContainerInterface
{
    /**
     * @param string $id
     */
    public function has($id);
}

final class SomeContainerBuilder extends SomeUniqueContainer
{
    public function has($id)
    {
    }
}

class SomeUniqueContainer implements SomeContainerInterface
{
    public function has($id)
    {
    }
}

?>
