<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use MyCLabs\Enum\Enum;

/**
 * @method static PrivateConstWithStaticMethod VIDEO()
 */
final class PrivateConstWithStaticMethod extends Enum
{
    /**
     * Some comment
     */
    private const VIDEO = 'video';
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use MyCLabs\Enum\Enum;

enum PrivateConstWithStaticMethod
{
    /**
     * Some comment
     */
    case VIDEO = 'video';
}

?>
