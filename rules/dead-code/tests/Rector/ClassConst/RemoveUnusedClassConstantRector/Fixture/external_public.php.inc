<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class ExternalPublic
{
    public const SOME_CONST = 'dead';
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\Fixture;

class ExternalPublic
{
}

?>
