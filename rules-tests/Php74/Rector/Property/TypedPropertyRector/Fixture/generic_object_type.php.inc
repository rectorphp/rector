<?php

/**
 * @template T of object
 */
namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class GenericObjectType
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

/**
 * @template T of object
 */
namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class GenericObjectType
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
