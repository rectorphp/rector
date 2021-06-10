<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use stdClass;

function test()
{
    return new stdClass;
}

class SomeFuncCall
{
    public function run()
    {
        return test()::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

use stdClass;

function test()
{
    return new stdClass;
}

class SomeFuncCall
{
    public function run()
    {
        return get_class(test());
    }
}

?>
