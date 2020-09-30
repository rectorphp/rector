<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;

interface ExpectedNameResolverInterface
{
    public function resolveIfNotYet(Node $node): ?string;

    public function resolve(Node $node): ?string;
}
