<?php

namespace Rector\Tests\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector\Fixture;

class SomeClass
{
    final public const NAME = 'value';
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector\Fixture;

class SomeClass
{
    public const NAME = 'value';
}

?>
