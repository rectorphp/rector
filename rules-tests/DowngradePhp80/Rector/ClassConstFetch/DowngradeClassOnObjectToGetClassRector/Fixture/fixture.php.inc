<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

class Fixture
{
    public function run($object)
    {
        return $object::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

class Fixture
{
    public function run($object)
    {
        return get_class($object);
    }
}

?>
