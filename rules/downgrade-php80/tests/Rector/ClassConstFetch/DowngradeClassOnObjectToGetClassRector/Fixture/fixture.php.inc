<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

class SomeClass
{
    public function run($object)
    {
        return $object::class;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\Fixture;

class SomeClass
{
    public function run($object)
    {
        return get_class($object);
    }
}

?>
