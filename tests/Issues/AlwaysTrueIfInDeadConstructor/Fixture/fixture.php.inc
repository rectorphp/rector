<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\AlwaysTrueIfInDeadConstructor\Fixture;

final class Fixture
{
    public function __construct()
    {
        if (1 === 1) {
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\AlwaysTrueIfInDeadConstructor\Fixture;

final class Fixture
{
}

?>
