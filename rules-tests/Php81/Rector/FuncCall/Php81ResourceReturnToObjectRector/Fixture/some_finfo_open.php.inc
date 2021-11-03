<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeFinfoOpen
{
    public function run()
    {
        $f = finfo_open();
        is_resource($f);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeFinfoOpen
{
    public function run()
    {
        $f = finfo_open();
        $f instanceof \finfo;
    }
}

?>
