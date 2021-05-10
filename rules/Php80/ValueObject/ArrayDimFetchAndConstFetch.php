<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ConstFetch;

final class ArrayDimFetchAndConstFetch
{
    public function __construct(
        private ArrayDimFetch $arrayDimFetch,
        private ConstFetch $constFetch
    ) {
    }

    public function getArrayDimFetch(): ArrayDimFetch
    {
        return $this->arrayDimFetch;
    }

    public function getConstFetch(): ConstFetch
    {
        return $this->constFetch;
    }
}
