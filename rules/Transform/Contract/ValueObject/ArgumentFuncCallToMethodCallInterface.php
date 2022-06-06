<?php

declare (strict_types=1);
namespace Rector\Transform\Contract\ValueObject;

interface ArgumentFuncCallToMethodCallInterface
{
    public function getFunction() : string;
}
