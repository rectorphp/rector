<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ConstructorOnlyStatic
{
    /**
     * @var int
     */
    private static $contentFinder;

    public function __construct(int $contentFinder)
    {
        self::$contentFinder = $contentFinder;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class ConstructorOnlyStatic
{
    public function __construct()
    {
    }
}

?>
