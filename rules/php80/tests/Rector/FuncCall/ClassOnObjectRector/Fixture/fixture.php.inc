<?php

namespace Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector\Fixture;

class Fixture
{
    public function run($object)
    {
        return $object::class;
    }
}

?>
