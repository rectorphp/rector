<?php

namespace Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Fixture;

use Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Source\SomeExternalObject;

final class WithPropertyFetch
{
    private SomeExternalObject $someExternalObject;

    public function __construct(SomeExternalObject $someExternalObject)
    {
        $this->someExternalObject = $someExternalObject;
    }

    public function run()
    {
        $name = [$this->someExternalObject, 'sleepOver'];
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Fixture;

use Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Source\SomeExternalObject;

final class WithPropertyFetch
{
    private SomeExternalObject $someExternalObject;

    public function __construct(SomeExternalObject $someExternalObject)
    {
        $this->someExternalObject = $someExternalObject;
    }

    public function run()
    {
        $name = $this->someExternalObject->sleepOver(...);
    }
}

?>
