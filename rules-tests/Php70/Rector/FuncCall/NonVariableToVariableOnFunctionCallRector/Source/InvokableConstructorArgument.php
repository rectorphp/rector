<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Source;

final class InvokableConstructorArgument
{
    public function __invoke(&$bar)
    {
    }
}
