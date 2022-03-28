<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

trait SomeTrait
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}

?>
-----
<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

trait SomeTrait
{
    public function callOnMe()
    {
        var_dump( static::class);
    }
}

?>
