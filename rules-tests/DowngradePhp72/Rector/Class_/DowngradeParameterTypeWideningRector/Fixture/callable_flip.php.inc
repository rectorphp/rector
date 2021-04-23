<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

/**
 * @see https://3v4l.org/8B03D
 */
interface SomeAskingInterface
{
    public function ask(callable $callable = null);
}

final class Asker implements SomeAskingInterface
{
    public function ask($callable = null)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

/**
 * @see https://3v4l.org/8B03D
 */
interface SomeAskingInterface
{
    /**
     * @param callable|null $callable
     */
    public function ask($callable = null);
}

final class Asker implements SomeAskingInterface
{
    public function ask($callable = null)
    {
    }
}

?>
