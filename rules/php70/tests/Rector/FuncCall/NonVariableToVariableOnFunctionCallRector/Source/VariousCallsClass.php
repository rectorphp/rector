<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class VariousCallsClass
{
    public static function staticMethod(&$bar) {}

    public function baz(&$bar) {}

    public function child(): ChildClass
    {
        return new ChildClass();
    }
}
