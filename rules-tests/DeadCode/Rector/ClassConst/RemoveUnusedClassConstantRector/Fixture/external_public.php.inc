<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class ExternalPublic
{
    public const SOME_CONST = 'dead';
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class ExternalPublic
{
}

?>
