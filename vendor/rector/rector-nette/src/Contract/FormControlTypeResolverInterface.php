<?php

declare (strict_types=1);
namespace Rector\Nette\Contract;

use PhpParser\Node;
interface FormControlTypeResolverInterface
{
    /**
     * @return array<string, class-string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array;
}
