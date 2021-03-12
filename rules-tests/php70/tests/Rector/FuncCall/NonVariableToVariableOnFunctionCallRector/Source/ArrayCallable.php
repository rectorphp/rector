<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class ArrayCallable
{
    public static function someStaticMethod(&$bar)
    {
    }

    public function someMethod(&$bar)
    {
    }
}
