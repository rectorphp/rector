<?php

namespace Rector\Tests\Php81\Rector\ClassConst\FinalizePublicClassConstantRector\Fixture;

class SomeClass
{
    public const NAME = 'value';
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\ClassConst\FinalizePublicClassConstantRector\Fixture;

class SomeClass
{
    public final const NAME = 'value';
}

?>
