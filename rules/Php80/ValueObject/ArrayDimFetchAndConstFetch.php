<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ConstFetch;

final class ArrayDimFetchAndConstFetch
{
    /**
     * @var ArrayDimFetch
     */
    private $arrayDimFetch;

    /**
     * @var ConstFetch
     */
    private $constFetch;

    public function __construct(ArrayDimFetch $arrayDimFetch, ConstFetch $constFetch)
    {
        $this->arrayDimFetch = $arrayDimFetch;
        $this->constFetch = $constFetch;
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
