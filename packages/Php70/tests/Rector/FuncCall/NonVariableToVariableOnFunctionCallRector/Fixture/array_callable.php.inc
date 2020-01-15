<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ArrayCallable
{
    public static function someStaticMethod(&$bar) {}
    public function someMethod(&$bar) {}
}

function arrayCallable()
{
    [ArrayCallable::class, 'someStaticMethod'](bar());
    $callable = [ArrayCallable::class, 'someStaticMethod'];
    $callable(bar());

    $arrayCallable = new ArrayCallable();
    [$arrayCallable, 'someMethod'](bar());
    $callable = [$arrayCallable, 'someMethod'];
    $callable(bar());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

class ArrayCallable
{
    public static function someStaticMethod(&$bar) {}
    public function someMethod(&$bar) {}
}

function arrayCallable()
{
    $bar = bar();
    [ArrayCallable::class, 'someStaticMethod']($bar);
    $callable = [ArrayCallable::class, 'someStaticMethod'];
    $bar = bar();
    $callable($bar);

    $arrayCallable = new ArrayCallable();
    $bar = bar();
    [$arrayCallable, 'someMethod']($bar);
    $callable = [$arrayCallable, 'someMethod'];
    $bar = bar();
    $callable($bar);
}

?>
