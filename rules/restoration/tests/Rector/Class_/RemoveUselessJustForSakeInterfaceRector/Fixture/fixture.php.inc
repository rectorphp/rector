<?php

namespace Rector\Restoration\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector\Fixture;

class SomeClass implements OnlyHereUsedInterface
{
}

interface OnlyHereUsedInterface
{
}

class SomePresenter
{
    public function __construct(OnlyHereUsedInterface $onlyHereUsed)
    {
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector\Fixture;

class SomeClass
{
}

class SomePresenter
{
    public function __construct(\Rector\Restoration\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector\Fixture\SomeClass $onlyHereUsed)
    {
    }
}

?>
