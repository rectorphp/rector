<?php

declare (strict_types=1);
namespace Rector\PSR4\Contract;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
interface PSR4AutoloadNamespaceMatcherInterface
{
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \PhpParser\Node $node
     */
    public function getExpectedNamespace($file, $node) : ?string;
}
