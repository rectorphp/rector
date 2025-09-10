<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeTransformer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Symfony\Enum\SymfonyClass;
final class OutputInputSymfonyStyleReplacer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function replace(ClassMethod $executeClassMethod): void
    {
        $symfonyStyleVariableName = $this->matchSymfonyStyleNewVariableName($executeClassMethod);
        // nothing to update here
        if ($symfonyStyleVariableName === null) {
            return;
        }
        // 1. add symfony style param
        $executeClassMethod->params[] = new Param(new Variable($symfonyStyleVariableName), null, new FullyQualified(SymfonyClass::SYMFONY_STYLE));
        // 2. remove new symfony style inside
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($executeClassMethod, function (Node $node): ?int {
            if (!$node instanceof Expression) {
                return null;
            }
            if (!$node->expr instanceof Assign) {
                return null;
            }
            $assign = $node->expr;
            if (!$this->isSymfonyStyleNewAssign($assign)) {
                return null;
            }
            return NodeVisitor::REMOVE_NODE;
        });
    }
    private function matchSymfonyStyleNewVariableName(ClassMethod $executeClassMethod): ?string
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstancesOfScoped($executeClassMethod->stmts, Assign::class);
        foreach ($assigns as $assign) {
            if (!$this->isSymfonyStyleNewAssign($assign)) {
                continue;
            }
            if (!$assign->var instanceof Variable) {
                continue;
            }
            return $this->nodeNameResolver->getName($assign->var);
        }
        return null;
    }
    private function isSymfonyStyleNewAssign(Assign $assign): bool
    {
        if (!$assign->expr instanceof New_) {
            return \false;
        }
        $new = $assign->expr;
        return $this->nodeNameResolver->isName($new->class, SymfonyClass::SYMFONY_STYLE);
    }
}
