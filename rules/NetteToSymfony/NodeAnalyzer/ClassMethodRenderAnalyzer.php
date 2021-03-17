<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Nette\NodeAnalyzer\ReturnAnalyzer;
use Rector\Nette\NodeAnalyzer\ThisTemplatePropertyFetchAnalyzer;
use Rector\NetteToSymfony\ValueObject\ClassMethodRender;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodRenderAnalyzer
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
     * @var Expr[]
     */
    private $templateFileExprs = [];

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

    /**
     * @var ReturnAnalyzer
     */
    private $returnAnalyzer;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ScopeNestingComparator $scopeNestingComparator,
        BetterNodeFinder $betterNodeFinder,
        ThisTemplatePropertyFetchAnalyzer $thisTemplatePropertyFetchAnalyzer,
        ReturnAnalyzer $returnAnalyzer
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->thisTemplatePropertyFetchAnalyzer = $thisTemplatePropertyFetchAnalyzer;
        $this->returnAnalyzer = $returnAnalyzer;
    }

    public function collectFromClassMethod(ClassMethod $classMethod): ClassMethodRender
    {
        $this->templateFileExprs = [];
        $this->templateVariables = [];
        $this->nodesToRemove = [];
        $this->conditionalAssigns = [];

        $this->lastReturn = $this->returnAnalyzer->findLastClassMethodReturn($classMethod);

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node): void {
                if ($node instanceof MethodCall) {
                    $this->collectTemplateFileExpr($node);
                }

                if ($node instanceof Assign) {
                    $this->collectVariableFromAssign($node);
                }
            }
        );

        return new ClassMethodRender(
            $this->templateFileExprs,
            $this->templateVariables,
            $this->nodesToRemove,
            $this->conditionalAssigns
        );
    }

    private function collectTemplateFileExpr(MethodCall $methodCall): void
    {
        if (! $this->nodeNameResolver->isNames($methodCall->name, ['render', 'setFile'])) {
            return;
        }

        $this->nodesToRemove[] = $methodCall;
        if (! isset($methodCall->args[0])) {
            return;
        }

        $this->templateFileExprs[] = $methodCall->args[0]->value;
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
            if (! $this->returnAnalyzer->isBeforeLastReturn($assign, $this->lastReturn)) {
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
}
