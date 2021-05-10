<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
final class UsedVariableNameAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isVariableNamed(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\PropertyFetch) && ($node->name instanceof \PhpParser\Node\Expr\Variable && \is_string($node->name->name))) {
            return $this->nodeNameResolver->isName($variable, $node->name->name);
        }
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->nodeNameResolver->areNamesEqual($variable, $node);
    }
}
