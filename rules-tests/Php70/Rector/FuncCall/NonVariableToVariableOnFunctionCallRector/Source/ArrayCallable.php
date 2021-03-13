<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class ArrayCallable
{
    public static function someStaticMethod(&$bar)
    {
    }

    public function someMethod(&$bar)
    {
    }
}
