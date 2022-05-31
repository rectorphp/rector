<?php

namespace Rector\Core\Tests\Issues\DowngradeReadonlyClassPropertyToPhp80\Fixture;

final readonly class Fixture2
{
    public array $property;
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\DowngradeReadonlyClassPropertyToPhp80\Fixture;

final class Fixture2
{
    /**
     * @readonly
     */
    public array $property;
}

?>
