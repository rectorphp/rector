<?php

namespace Rector\Php74\Tests\Rector\Class_\ClassConstantToSelfClassRector\Fixture;

class Fixture
{
    public function callOnMe()
    {
        var_dump(__CLASS__);
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Class_\ClassConstantToSelfClassRector\Fixture;

class Fixture
{
    public function callOnMe()
    {
        var_dump(self::class);
    }
}

?>
