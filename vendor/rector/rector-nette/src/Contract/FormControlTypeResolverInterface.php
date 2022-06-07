<?php

declare (strict_types=1);
namespace Rector\Nette\Contract;

use PhpParser\Node;
interface FormControlTypeResolverInterface
{
    /**
     * @return array<string, string>
     */
    public function resolve(Node $node) : array;
}
