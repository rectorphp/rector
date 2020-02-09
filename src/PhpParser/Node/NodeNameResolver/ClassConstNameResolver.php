<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class ClassConstNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireClassNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return ClassConst::class;
    }

    /**
     * @param ClassConst $node
     */
    public function resolve(Node $node): ?string
    {
        if (count($node->consts) === 0) {
            return null;
        }

        $onlyConstant = $node->consts[0];

        return $this->nodeNameResolver->getName($onlyConstant);
    }
}
