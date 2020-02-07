<?php

namespace Rector\Core\Tests\Rector\Property\PropertyToMethodRector\Fixture;

final class SomeClassWithParameters
{
    private $parameter;

    public function run()
    {
        $value = $this->parameter;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Property\PropertyToMethodRector\Fixture;

final class SomeClassWithParameters
{
    private $parameter;

    public function run()
    {
        $value = $this->getConfig('parameter');
    }
}

?>
