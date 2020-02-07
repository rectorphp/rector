<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

class Promise
{
}

class Foo
{
}

final class NonArrayGenericTypes
{
    /**
     * @var \Traversable<int, string>
     */
    private $foo;

    /**
     * @var Promise<Foo>
     */
    private $bar;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

class Promise
{
}

class Foo
{
}

final class NonArrayGenericTypes
{
    /**
     * @var \Traversable<int, string>
     */
    private \Traversable $foo;

    /**
     * @var Promise<Foo>
     */
    private \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture\Promise $bar;
}

?>
