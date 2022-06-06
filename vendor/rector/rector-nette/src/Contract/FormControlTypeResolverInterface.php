<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Contract;

use RectorPrefix20220606\PhpParser\Node;
interface FormControlTypeResolverInterface
{
    /**
     * @return array<string, string>
     */
    public function resolve(Node $node) : array;
}
