<?php

namespace Rector\Php74\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

class Fixture
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

class Fixture
{
    public function callOnMe()
    {
        var_dump( static::class);
    }
}

?>
