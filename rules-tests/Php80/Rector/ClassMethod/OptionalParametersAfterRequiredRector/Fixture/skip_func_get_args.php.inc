<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class SkipFuncGetArgs
{
    public function go($required)
    {
        $args = func_get_args();
        return $args;
    }

    public function create()
    {
        return $this->go(1, 5, 100);
    }
}
