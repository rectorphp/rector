<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\ArrayCallable;

final class SomeArrayCallable
{
    public function run()
    {
        [ArrayCallable::class, 'someStaticMethod'](bar());
        $callable = [ArrayCallable::class, 'someStaticMethod'];
        $callable(bar());

        $arrayCallable = new ArrayCallable();
        [$arrayCallable, 'someMethod'](bar());
        $callable = [$arrayCallable, 'someMethod'];
        $callable(bar());
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

use Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source\ArrayCallable;

final class SomeArrayCallable
{
    public function run()
    {
        $bar = bar();
        [ArrayCallable::class, 'someStaticMethod']($bar);
        $callable = [ArrayCallable::class, 'someStaticMethod'];
        $bar2 = bar();
        $callable($bar2);

        $arrayCallable = new ArrayCallable();
        $bar3 = bar();
        [$arrayCallable, 'someMethod']($bar3);
        $callable = [$arrayCallable, 'someMethod'];
        $bar4 = bar();
        $callable($bar4);
    }
}

?>
