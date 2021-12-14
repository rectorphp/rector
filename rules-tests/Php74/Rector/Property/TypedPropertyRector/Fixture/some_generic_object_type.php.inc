<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

/**
 * @template TType of object
 */
final class SomeGenericObjectType
{
    /**
     * @var TType
     */
    private $command;

    /**
     * @param TType $command
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
 * @template TType of object
 */
final class SomeGenericObjectType
{
    /**
     * @var TType
     */
    private object $command;

    /**
     * @param TType $command
     */
    public function __construct(object $command)
    {
        $this->command = $command;
    }
}
?>
