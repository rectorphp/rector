<?php

declare(strict_types=1);

namespace Rector\PSR4\Contract;

use PhpParser\Node;

interface PSR4AutoloadNamespaceMatcherInterface
{
    public function getExpectedNamespace(Node $node): ?string;
}
