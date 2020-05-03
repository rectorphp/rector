<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnPrivateProperty;


final class SomeController
{
    private $unusedProperty = [];

    /**
     * @noRector \Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector
     */
    private $anotherUnusedProperty = [];

}

?>
    -----
<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnPrivateProperty;


final class SomeController
{
    /**
     * @noRector \Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector
     */
    private $anotherUnusedProperty = [];

}

?>
