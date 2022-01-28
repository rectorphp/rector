<?php

namespace Rector\Tests\Php81\Rector\ClassConst\FinalizePublicClassConstantRector\Fixture;

abstract class ParentClass
{
    public const TEST_CONSTANT = 'value1';
}

class ChildClass extends ParentClass
{
    public const TEST_CONSTANT = 'value2';
}
?>
-----
<?php

namespace Rector\Tests\Php81\Rector\ClassConst\FinalizePublicClassConstantRector\Fixture;

abstract class ParentClass
{
    public const TEST_CONSTANT = 'value1';
}

class ChildClass extends ParentClass
{
    public final const TEST_CONSTANT = 'value2';
}
?>
