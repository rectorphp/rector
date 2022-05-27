<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeNameResolver\NodeNameResolver;
final class StaticCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isParentCallNamed(\PhpParser\Node $node, string $desiredMethodName) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        if ($node->class instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node->class, 'parent')) {
            return \false;
        }
        if ($node->name instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, $desiredMethodName);
    }
}
