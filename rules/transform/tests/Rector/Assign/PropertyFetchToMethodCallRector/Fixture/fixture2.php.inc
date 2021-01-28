<?php

namespace Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

final class Fixture2
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

namespace Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

final class Fixture2
{
    private $parameter;

    public function run()
    {
        $value = $this->getConfig('parameter');
    }
}

?>
