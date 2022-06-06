<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PSR4\Contract;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
interface PSR4AutoloadNamespaceMatcherInterface
{
    public function getExpectedNamespace(File $file, Node $node) : ?string;
}
