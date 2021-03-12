<?php

namespace Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector\Fixture;

class WithoutParameterInsideClass
{
    public function run()
    {
        return self::class;
    }
}

?>
