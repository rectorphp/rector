<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class ChildClass
{
    public function bar(&$bar) {}
}
