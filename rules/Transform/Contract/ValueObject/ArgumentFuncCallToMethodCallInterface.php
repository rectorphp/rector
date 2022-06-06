<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\Contract\ValueObject;

interface ArgumentFuncCallToMethodCallInterface
{
    public function getFunction() : string;
}
