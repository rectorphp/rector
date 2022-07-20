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
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
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
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(IfManipulator $ifManipulator, PropertyFetchAnalyzer $propertyFetchAnalyzer, ConstructorAssignDetector $constructorAssignDetector, PromotedPropertyResolver $promotedPropertyResolver, ContextAnalyzer $contextAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Stmt[]|null|If_
     */
    public function refactor(Node $node)
    {
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }
        if ($this->contextAnalyzer->isInLoop($node)) {
            return null;
        }
        $originalCondNode = $node->cond->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalCondNode instanceof Node) {
            return null;
        }
        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond->expr);
        }
        if ($node->cond instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond);
        }
        return null;
    }
    /**
     * @return Stmt[]|null
     */
    private function processMayDeadInstanceOf(If_ $if, Instanceof_ $instanceof)
    {
        if (!$instanceof->class instanceof Name) {
            return null;
        }
        $classType = $this->nodeTypeResolver->getType($instanceof->class);
        $exprType = $this->nodeTypeResolver->getType($instanceof->expr);
        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)->yes();
        if (!$isSameStaticTypeOrSubtype) {
            return null;
        }
        if (!$instanceof->expr instanceof Variable && !$this->isInPropertyPromotedParams($instanceof->expr) && $this->isSkippedPropertyFetch($instanceof->expr)) {
            return null;
        }
        if ($this->shouldSkipFromNotTypedParam($instanceof)) {
            return null;
        }
        if ($if->cond === $instanceof && $if->stmts !== []) {
            return $if->stmts;
        }
        $this->removeNode($if);
        return $if;
    }
    private function shouldSkipFromNotTypedParam(Instanceof_ $instanceof) : bool
    {
        $functionLike = $this->betterNodeFinder->findParentType($instanceof, FunctionLike::class);
        if (!$functionLike instanceof FunctionLike) {
            return \false;
        }
        $variable = $instanceof->expr;
        $isReAssign = (bool) $this->betterNodeFinder->findFirstPrevious($instanceof, function (Node $subNode) use($variable) : bool {
            return $subNode instanceof Assign && $this->nodeComparator->areNodesEqual($subNode->var, $variable);
        });
        if ($isReAssign) {
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
    private function isSkippedPropertyFetch(Expr $expr) : bool
    {
        if (!$this->propertyFetchAnalyzer->isPropertyFetch($expr)) {
            return \true;
        }
        /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
        $propertyFetch = $expr;
        $classLike = $this->betterNodeFinder->findParentType($propertyFetch, Class_::class);
        if (!$classLike instanceof Class_) {
            return \true;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof Property) {
            return \true;
        }
        $isPropertyAssignedInConstuctor = $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
        return $property->type === null && !$isPropertyAssignedInConstuctor;
    }
    private function isInPropertyPromotedParams(Expr $expr) : bool
    {
        if (!$expr instanceof PropertyFetch) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($expr, Class_::class);
        if (!$classLike instanceof Class_) {
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
