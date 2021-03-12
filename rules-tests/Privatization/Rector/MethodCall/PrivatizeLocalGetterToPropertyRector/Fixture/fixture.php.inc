<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture;

class Fixture
{
    private $some;

    public function run()
    {
        return $this->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\Fixture;

class Fixture
{
    private $some;

    public function run()
    {
        return $this->some + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}

?>
