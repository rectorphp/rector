<?php

declare (strict_types=1);
namespace Rector\PSR4\Contract;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
interface PSR4AutoloadNamespaceMatcherInterface
{
    public function getExpectedNamespace(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : ?string;
}
