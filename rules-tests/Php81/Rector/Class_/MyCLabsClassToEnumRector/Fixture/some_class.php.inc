<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use MyCLabs\Enum\Enum;

final class SomeClass extends Enum
{
    /**
     * Some comment
     */
    private const VIEW = 'view';

    private const EDIT = 'edit';
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use MyCLabs\Enum\Enum;

enum SomeClass
{
    /**
     * Some comment
     */
    case VIEW = 'view';
    case EDIT = 'edit';
}

?>
