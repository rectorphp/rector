<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/test/unit/NodeCompiler/CompileNodeToValueTest.php
 */
final class NodeValueResolver
{
    /**
     * @var PerNodeValueResolverInterface[]
     */
    private $perNodeValueResolvers = [];

    public function addPerNodeValueResolver(PerNodeValueResolverInterface $perNodeValueResolver): void
    {
        $this->perNodeValueResolvers[$perNodeValueResolver->getNodeClass()] = $perNodeValueResolver;
    }

    /**
     * @return string|bool|null
     */
    public function resolve(Node $node)
    {
        $nodeClass = get_class($node);
        if (!isset($this->perNodeValueResolvers[$nodeClass])) {
            return null;
        }

        return $this->perNodeValueResolvers[$nodeClass]->resolve($node);
    }
}
