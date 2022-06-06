<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class UsedVariableNameAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isVariableNamed(Node $node, Variable $variable) : bool
    {
        if (($node instanceof MethodCall || $node instanceof PropertyFetch) && ($node->name instanceof Variable && \is_string($node->name->name))) {
            return $this->nodeNameResolver->isName($variable, $node->name->name);
        }
        if (!$node instanceof Variable) {
            return \false;
        }
        return $this->nodeNameResolver->areNamesEqual($variable, $node);
    }
}
