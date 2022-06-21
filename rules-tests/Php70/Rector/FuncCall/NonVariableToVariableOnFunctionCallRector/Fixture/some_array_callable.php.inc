<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\ClassWithArrayCallable;

final class SomeArrayCallable
{
    public function run()
    {
        [ClassWithArrayCallable::class, 'someStaticMethod'](bar());
        $callable = [ClassWithArrayCallable::class, 'someStaticMethod'];
        $callable(bar());

        $arrayCallable = new ClassWithArrayCallable();
        [$arrayCallable, 'someMethod'](bar());
        $callable = [$arrayCallable, 'someMethod'];
        $callable(bar());
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\ClassWithArrayCallable;

final class SomeArrayCallable
{
    public function run()
    {
        $bar = bar();
        [ClassWithArrayCallable::class, 'someStaticMethod']($bar);
        $callable = [ClassWithArrayCallable::class, 'someStaticMethod'];
        $bar2 = bar();
        $callable($bar2);

        $arrayCallable = new ClassWithArrayCallable();
        $bar3 = bar();
        [$arrayCallable, 'someMethod']($bar3);
        $callable = [$arrayCallable, 'someMethod'];
        $bar4 = bar();
        $callable($bar4);
    }
}

?>
