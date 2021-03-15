<?php

namespace Rector\Tests\Php80\Rector\FuncCall\ClassOnObjectRector\Fixture;

class Fixture
{
    public function run($object)
    {
        return get_class($object);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\ClassOnObjectRector\Fixture;

class Fixture
{
    public function run($object)
    {
        return $object::class;
    }
}

?>
