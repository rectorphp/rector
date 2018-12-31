<?php

namespace Rector\Php\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

class SomeClass
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

class SomeClass
{
    public function callOnMe()
    {
        var_dump( static::class);
    }
}

?>
