<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Naming\Contract\ExpectedNameResolver\ExpectedNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

abstract class AbstractExpectedNameResolver implements ExpectedNameResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @required
     */
    public function autowireAbstractExpectedNameResolver(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ): void {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param Param|Property $node
     */
    public function resolveIfNotYet(Node $node): ?string
    {
        $expectedName = $this->resolve($node);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($node);

        if ($this->nodeNameResolver->endsWith($currentName, $expectedName)) {
            return null;
        }

        if ($this->nodeNameResolver->isName($node, $expectedName)) {
            return null;
        }

        return $expectedName;
    }
}
