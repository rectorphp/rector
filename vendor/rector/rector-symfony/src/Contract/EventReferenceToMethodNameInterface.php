<?php

declare (strict_types=1);
namespace Rector\Symfony\Contract;

use PhpParser\Node\Expr\ClassConstFetch;
interface EventReferenceToMethodNameInterface
{
    public function getClassConstFetch() : \PhpParser\Node\Expr\ClassConstFetch;
    public function getMethodName() : string;
}
