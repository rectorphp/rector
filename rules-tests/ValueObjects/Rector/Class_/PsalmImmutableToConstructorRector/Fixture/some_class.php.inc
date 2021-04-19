<?php

namespace Rector\Tests\ValueObjects\Rector\Class_\PsalmImmutableToConstructorRector\Fixture;

class SomeClass
{
    /**
     * @psalm-immutable
     */
    public string $name;
}

?>
-----
<?php

namespace Rector\Tests\ValueObjects\Rector\Class_\PsalmImmutableToConstructorRector\Fixture;

class SomeClass
{
    /**
     * @psalm-immutable
     */
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>
