<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class EventSubscriberMethodNamesResolver
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @return string[]
     */
    public function resolveFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $methodNames = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use(&$methodNames) {
            if (!$node instanceof \PhpParser\Node\Expr\ArrayItem) {
                return null;
            }
            if (!$node->value instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $methodNames[] = $node->value->value;
        });
        return $methodNames;
    }
}
