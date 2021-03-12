<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class Fixture
{
    public $value;

    public function run()
    {
        return $this->value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class Fixture
{
    private $value;

    public function run()
    {
        return $this->value;
    }
}

?>
