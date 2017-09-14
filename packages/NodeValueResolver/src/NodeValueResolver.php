<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
use Rector\Exception\NotImplementedException;
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
        $this->perNodeValueResolvers[] = $perNodeValueResolver;
    }

    /**
     * @return mixed
     */
    public function resolve(Node $node)
    {
        foreach ($this->perNodeValueResolvers as $perNodeValueResolver) {
            if (! is_a($node, $perNodeValueResolver->getNodeClass(), true)) {
                continue;
            }

            return $perNodeValueResolver->resolve($node);
        }

        throw new NotImplementedException(sprintf(
            '%s() was unable to resolve "%s" Node. Add new value resolver via addValueResolver() method.',
            __METHOD__,
            get_class($node)
        ));
    }
}
