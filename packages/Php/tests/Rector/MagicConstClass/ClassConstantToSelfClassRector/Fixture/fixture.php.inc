<?php

namespace Rector\Php\Tests\Rector\ConstFetch\ClassConstantToSelfClassRector\Fixture;

class SomeClass
{
    public function callOnMe()
    {
        var_dump(__CLASS__);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ConstFetch\ClassConstantToSelfClassRector\Fixture;

class SomeClass
{
    public function callOnMe()
    {
        var_dump(self::class);
    }
}

?>
