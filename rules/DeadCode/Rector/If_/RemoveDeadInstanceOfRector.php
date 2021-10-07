<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector, \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver $promotedPropertyResolver, \Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove dead instanceof check on type hinted variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        if (! $stdClass instanceof stdClass) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        return true;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Stmt\If_
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // a trait
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }
        if ($this->contextAnalyzer->isInLoop($node)) {
            return null;
        }
        if ($node->cond instanceof \PhpParser\Node\Expr\BooleanNot && $node->cond->expr instanceof \PhpParser\Node\Expr\Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond->expr);
        }
        if ($node->cond instanceof \PhpParser\Node\Expr\Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond);
        }
        return $node;
    }
    private function processMayDeadInstanceOf(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\Instanceof_ $instanceof) : ?\PhpParser\Node\Stmt\If_
    {
        if (!$instanceof->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        $classType = $this->nodeTypeResolver->getType($instanceof->class);
        $exprType = $this->nodeTypeResolver->getType($instanceof->expr);
        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)->yes();
        if (!$isSameStaticTypeOrSubtype) {
            return null;
        }
        if (!$instanceof->expr instanceof \PhpParser\Node\Expr\Variable && !$this->isInPropertyPromotedParams($instanceof->expr) && $this->isSkippedPropertyFetch($instanceof->expr)) {
            return null;
        }
        if ($this->shouldSkipFromNotTypedParam($instanceof)) {
            return null;
        }
        if ($if->cond === $instanceof) {
            $this->nodesToAddCollector->addNodesBeforeNode($if->stmts, $if);
        }
        $this->removeNode($if);
        return $if;
    }
    private function shouldSkipFromNotTypedParam(\PhpParser\Node\Expr\Instanceof_ $instanceof) : bool
    {
        $functionLike = $this->betterNodeFinder->findParentType($instanceof, \PhpParser\Node\FunctionLike::class);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
            return \false;
        }
        $variable = $instanceof->expr;
        $isReassign = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($instanceof, function (\PhpParser\Node $subNode) use($variable) : bool {
            return $subNode instanceof \PhpParser\Node\Expr\Assign && $this->nodeComparator->areNodesEqual($subNode->var, $variable);
        });
        if ($isReassign) {
            return \false;
        }
        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if ($this->nodeComparator->areNodesEqual($param->var, $instanceof->expr)) {
                return $param->type === null;
            }
        }
        return \false;
    }
    private function isSkippedPropertyFetch(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expr)) {
            return \true;
        }
        /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
        $propertyFetch = $expr;
        $classLike = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return \true;
        }
        $isFilledByConstructParam = $this->propertyFetchAnalyzer->isFilledByConstructParam($property);
        if ($this->isInPropertyPromotedParams($propertyFetch)) {
            return \false;
        }
        $isPropertyAssignedInConstuctor = $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
        return $property->type === null && !$isPropertyAssignedInConstuctor && !$isFilledByConstructParam;
    }
    private function isInPropertyPromotedParams(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        $classLike = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($expr);
        $params = $this->promotedPropertyResolver->resolveFromClass($classLike);
        foreach ($params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
}
