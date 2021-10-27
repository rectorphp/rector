<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeImageCreate
{
    public function run()
    {
        $gd = imagecreate(1, 2);
        is_resource($gd);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeImageCreate
{
    public function run()
    {
        $gd = imagecreate(1, 2);
        $gd instanceof \GdImage;
    }
}

?>
