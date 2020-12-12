<?php

namespace Rector\Order\Tests\Rector\Class_\OrderConstantsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    private const PRIVATE_CONST = 'private';
    protected const PROTECTED_CONST = 'protected';
    public const PUBLIC_CONST = 'public';
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderConstantsByVisibilityRector\Fixture;

abstract class AbstractClass
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
}

?>
