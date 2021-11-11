<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveParameterOnConstructorOnlyInMiddleParams
{
    /**
     * @var int
     */
    private $contentFinder;

    public function __construct(int $contentFinder, \stdClass $stdClass)
    {
        $this->contentFinder = $contentFinder;
        $this->init($stdClass);
    }

    private function init(\stdClass $stdClass)
    {
        var_dump($stdClass);
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class DoNotRemoveParameterOnConstructorOnlyInMiddleParams
{
    public function __construct(int $contentFinder, \stdClass $stdClass)
    {
        $this->init($stdClass);
    }

    private function init(\stdClass $stdClass)
    {
        var_dump($stdClass);
    }
}

?>
