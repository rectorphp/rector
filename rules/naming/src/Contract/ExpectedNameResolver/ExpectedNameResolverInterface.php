<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;

interface ExpectedNameResolverInterface
{
    /**
     * @param Param|Property $node
     */
    public function resolveIfNotYet(Node $node): ?string;

    /**
     * @param Param|Property $node
     */
    public function resolve(Node $node): ?string;
}
