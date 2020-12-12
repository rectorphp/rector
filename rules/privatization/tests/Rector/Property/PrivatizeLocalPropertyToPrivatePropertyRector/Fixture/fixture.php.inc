<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class SomeClass
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

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

class SomeClass
{
    private $value;

    public function run()
    {
        return $this->value;
    }
}

?>
