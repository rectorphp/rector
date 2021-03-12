<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\SetStateToStaticRector\Fixture;

class NoStatic
{
    public function __set_state($array)
    {
        return new NoStatic();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\SetStateToStaticRector\Fixture;

class NoStatic
{
    public static function __set_state($array)
    {
        return new NoStatic();
    }
}

?>
