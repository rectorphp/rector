<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

/**
 * @template T of object
 */
final class SomeGenericObjectType
{
    /**
     * @var T
     */
    private $command;

    /**
     * @param T $command
     */
    public function __construct(object $command)
    {
        $this->command = $command;
    }
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

/**
 * @template T of object
 */
final class SomeGenericObjectType
{
    /**
     * @var T
     */
    private object $command;

    /**
     * @param T $command
     */
    public function __construct(object $command)
    {
        $this->command = $command;
    }
}
?>
