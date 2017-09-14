<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
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

        if ($node instanceof Concat) {
            return $this->resolve($node->left) . $this->resolve($node->right);
        }

        throw new NotImplementedException(sprintf(
            '%s() was unable to resolve "%s" Node. Add new value resolver via addValueResolver() method.',
            __METHOD__,
            get_class($node)
        ));
    }
}
