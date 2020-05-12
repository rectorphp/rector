<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SomeClass
{
    private $count;
    public function run()
    {
        $this->count = 5;
        return $this->count;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

class SomeClass
{
    public function run()
    {
        $count = 5;
        return $count;
    }
}

?>
