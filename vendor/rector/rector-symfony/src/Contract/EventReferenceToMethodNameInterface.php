<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Contract;

use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
interface EventReferenceToMethodNameInterface
{
    public function getClassConstFetch() : ClassConstFetch;
    public function getMethodName() : string;
}
