<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector\Fixture;

final class Fixture
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}

?>
-----
<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector\Fixture;

final class Fixture
{
    public function callOnMe()
    {
        var_dump( self::class);
    }
}

?>
