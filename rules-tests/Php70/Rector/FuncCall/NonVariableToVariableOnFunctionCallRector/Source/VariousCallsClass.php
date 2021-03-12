<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class VariousCallsClass
{
    public static function staticMethod(&$bar) {}

    public function baz(&$bar) {}

    public function child(): ChildClass
    {
        return new ChildClass();
    }
}
