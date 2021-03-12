<?php

namespace Rector\Tests\Php80\Rector\FuncCall\ClassOnObjectRector\Fixture;

class WithoutParameterInsideClass
{
    public function run()
    {
        return get_class();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\ClassOnObjectRector\Fixture;

class WithoutParameterInsideClass
{
    public function run()
    {
        return self::class;
    }
}

?>
