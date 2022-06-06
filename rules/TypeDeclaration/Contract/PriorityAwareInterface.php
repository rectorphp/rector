<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract;

interface PriorityAwareInterface
{
    /**
     * Higher priority goes first.
     */
    public function getPriority() : int;
}
