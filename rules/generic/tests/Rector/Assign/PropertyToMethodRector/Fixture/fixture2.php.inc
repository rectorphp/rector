<?php

namespace Rector\Generic\Tests\Rector\Assign\PropertyToMethodRector\Fixture;

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

namespace Rector\Generic\Tests\Rector\Assign\PropertyToMethodRector\Fixture;

final class SomeClassWithParameters
{
    private $parameter;

    public function run()
    {
        $value = $this->getConfig('parameter');
    }
}

?>
