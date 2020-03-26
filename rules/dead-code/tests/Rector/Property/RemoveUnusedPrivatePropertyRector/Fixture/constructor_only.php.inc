<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ConstructorOnly
{
    /**
     * @var int
     */
    private $contentFinder;

    public function __construct(int $contentFinder)
    {
        $this->contentFinder = $contentFinder;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ConstructorOnly
{
    public function __construct()
    {
    }
}

?>
