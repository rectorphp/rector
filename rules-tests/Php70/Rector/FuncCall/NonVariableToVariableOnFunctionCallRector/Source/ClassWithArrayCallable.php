<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class ClassWithArrayCallable
{
    public static function someStaticMethod(&$bar)
    {
    }

    public function someMethod(&$bar)
    {
    }
}
