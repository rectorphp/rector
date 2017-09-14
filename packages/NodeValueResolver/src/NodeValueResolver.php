<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
use Rector\Exception\NotImplementedException;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\NodeAnalyzer\DynamicNodeAnalyzer;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/test/unit/NodeCompiler/CompileNodeToValueTest.php
 */
final class NodeValueResolver
{
    /**
     * @var PerNodeValueResolverInterface[]
     */
    private $perNodeValueResolvers = [];

    /**
     * @var DynamicNodeAnalyzer
     */
    private $dynamicNodeAnalyzer;

    public function __construct(DynamicNodeAnalyzer $dynamicNodeAnalyzer)
    {
        $this->dynamicNodeAnalyzer = $dynamicNodeAnalyzer;
    }

    public function addPerNodeValueResolver(PerNodeValueResolverInterface $perNodeValueResolver): void
    {
        $this->perNodeValueResolvers[] = $perNodeValueResolver;
    }

    /**
     * @return mixed
     */
    public function resolve(Node $node)
    {
        if ($this->dynamicNodeAnalyzer->isDynamicNode($node)) {
            return null;
        }

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
