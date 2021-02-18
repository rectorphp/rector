<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

interface ApiInterface {}

class IterableNamespacedInterfaceVarTag
{
    /**
     * @var iterable|ApiInterface[]
     */
    private $collection;

    public function __construct(iterable $collection)
    {
        $this->collection = $collection;
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

interface ApiInterface {}

class IterableNamespacedInterfaceVarTag
{
    /**
     * @var iterable|ApiInterface[]
     */
    private iterable $collection;

    public function __construct(iterable $collection)
    {
        $this->collection = $collection;
    }
}
?>
