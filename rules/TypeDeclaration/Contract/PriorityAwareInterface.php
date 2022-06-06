<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Contract;

interface PriorityAwareInterface
{
    /**
     * Higher priority goes first.
     */
    public function getPriority() : int;
}
