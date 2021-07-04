<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeAskingInterfaceWithNullable
{
    public function ask(?callable $callable = null);
}

final class AskForMore implements SomeAskingInterfaceWithNullable
{
    public function ask($callable = null)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeAskingInterfaceWithNullable
{
    /**
     * @param callable|null $callable
     */
    public function ask($callable = null);
}

final class AskForMore implements SomeAskingInterfaceWithNullable
{
    public function ask($callable = null)
    {
    }
}

?>
