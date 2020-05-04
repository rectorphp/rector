<?php

namespace Rector\Core\Tests\Issues\Issue3291\Fixture;


class Fixture3291KeepPrivateProperty
{
    private $unusedProperty = [];

    /**
     * @noRector \Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector
     * @noRector \Rector\DeadCode\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector
     */
    private $anotherUnusedProperty = [];
}

?>
    -----
<?php

namespace Rector\Core\Tests\Issues\Issue3291\Fixture;


class Fixture3291KeepPrivateProperty
{
    /**
     * @noRector \Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector
     * @noRector \Rector\DeadCode\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector
     */
    private $anotherUnusedProperty = [];
}

?>
