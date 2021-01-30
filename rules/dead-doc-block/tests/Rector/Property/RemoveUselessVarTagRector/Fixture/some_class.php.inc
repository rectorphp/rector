<?php

namespace Rector\DeadDocBlock\Tests\Rector\Property\RemoveUselessVarTagRector\Fixture;

final class SomeClass
{
    /**
     * @var string
     */
    public string $name = 'name';
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\Property\RemoveUselessVarTagRector\Fixture;

final class SomeClass
{
    public string $name = 'name';
}

?>
