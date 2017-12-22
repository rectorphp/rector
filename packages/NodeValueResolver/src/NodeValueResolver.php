<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
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
     * @return string|bool|null
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

        return null;
    }
}
