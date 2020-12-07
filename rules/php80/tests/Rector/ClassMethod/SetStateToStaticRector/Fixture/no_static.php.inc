<?php

namespace Rector\Php80\Tests\Rector\ClassMethod\SetStateToStaticRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\ClassMethod\SetStateToStaticRector\Fixture;

class NoStatic
{
    public static function __set_state($array)
    {
        return new NoStatic();
    }
}

?>
