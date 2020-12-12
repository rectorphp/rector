<?php

namespace Rector\DowngradePhp71\Tests\Rector\ClassConst\Fixture;

class Fixture
{
    const PUBLIC_CONST_A = 1;
    public const PUBLIC_CONST_B = 2;
    protected const PROTECTED_CONST = 3;
    private const PRIVATE_CONST = 4;
}

?>
-----
<?php

namespace Rector\DowngradePhp71\Tests\Rector\ClassConst\Fixture;

class Fixture
{
    const PUBLIC_CONST_A = 1;
    const PUBLIC_CONST_B = 2;
    const PROTECTED_CONST = 3;
    const PRIVATE_CONST = 4;
}

?>
