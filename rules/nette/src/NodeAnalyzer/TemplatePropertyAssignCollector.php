<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Nette\ValueObject\MagicTemplatePropertyCalls;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class TemplatePropertyAssignCollector
{
    /**
     * @var Expr[]
     */
    private $templateVariables = [];

    /**
     * @var Node[]
     */
    private $nodesToRemove = [];

    /**
     * @var array<string, Assign[]>
     */
    private $conditionalAssigns = [];

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ScopeNestingComparator
     */
    private $scopeNestingComparator;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ThisTemplatePropertyFetchAnalyzer
     */
    private $thisTemplatePropertyFetchAnalyzer;

    /**
     * @var Return_|null
     */
    private $lastReturn;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ScopeNestingComparator $scopeNestingComparator,
        BetterNodeFinder $betterNodeFinder,
        ThisTemplatePropertyFetchAnalyzer $thisTemplatePropertyFetchAnalyzer
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->thisTemplatePropertyFetchAnalyzer = $thisTemplatePropertyFetchAnalyzer;
    }

    public function collectMagicTemplatePropertyCalls(ClassMethod $classMethod): MagicTemplatePropertyCalls
    {
        $this->templateVariables = [];
        $this->nodesToRemove = [];
        $this->conditionalAssigns = [];

        $this->lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node): void {
                if ($node instanceof Assign) {
                    $this->collectVariableFromAssign($node);
                }
            }
        );

        return new MagicTemplatePropertyCalls(
            $this->templateVariables,
            $this->nodesToRemove,
            $this->conditionalAssigns
        );
    }

    private function collectVariableFromAssign(Assign $assign): void
    {
        // $this->template = x
        if ($assign->var instanceof PropertyFetch) {
            $propertyFetch = $assign->var;

            if (! $this->thisTemplatePropertyFetchAnalyzer->isTemplatePropertyFetch($propertyFetch->var)) {
                return;
            }

            $variableName = $this->nodeNameResolver->getName($propertyFetch);

            $foundParent = $this->betterNodeFinder->findParentTypes(
                $propertyFetch->var,
                ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES + [FunctionLike::class]
            );

            if ($foundParent && $this->scopeNestingComparator->isInBothIfElseBranch(
                $foundParent,
                $propertyFetch
            )) {
                $this->conditionalAssigns[$variableName][] = $assign;
                return;
            }

            if ($foundParent instanceof If_) {
                return;
            }

            if ($foundParent instanceof Else_) {
                return;
            }

            // there is a return before this assign, to do not remove it and keep ti
            if (! $this->isBeforeLastReturn($assign)) {
                return;
            }

            $this->templateVariables[$variableName] = $assign->expr;
            $this->nodesToRemove[] = $assign;
            return;
        }

        // $x = $this->template
        if (! $this->thisTemplatePropertyFetchAnalyzer->isTemplatePropertyFetch($assign->expr)) {
            return;
        }

        $this->nodesToRemove[] = $assign;
    }

    private function isBeforeLastReturn(Assign $assign): bool
    {
        if (! $this->lastReturn instanceof Return_) {
            return true;
        }

        return $this->lastReturn->getStartTokenPos() < $assign->getStartTokenPos();
    }
}
