<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class DemoFile
{
    /**
     * @var iterable|FormTypeInterface[]
     */
    private $formTypes;

    /**
     * @param FormTypeInterface[] $formTypes
     */
    public function __construct(iterable $formTypes)
    {
        $this->formTypes = $formTypes;
    }
}
?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class DemoFile
{
    /**
     * @var iterable|FormTypeInterface[]
     */
    private iterable $formTypes;

    /**
     * @param FormTypeInterface[] $formTypes
     */
    public function __construct(iterable $formTypes)
    {
        $this->formTypes = $formTypes;
    }
}
?>
