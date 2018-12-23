<?php

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class AssignConflict
{
    private $eventDispatcher;

    public function __construct(\EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(\Robocop $stdClass)
    {
        $this->eventDispatcher = $stdClass;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class AssignConflict
{
    /**
     * @var \EventDispatcher|\Robocop
     */
    private $eventDispatcher;

    public function __construct(\EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(\Robocop $stdClass)
    {
        $this->eventDispatcher = $stdClass;
    }
}

?>
