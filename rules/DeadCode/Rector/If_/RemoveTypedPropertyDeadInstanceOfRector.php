<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveTypedPropertyDeadInstanceOfRector\RemoveTypedPropertyDeadInstanceOfRectorTest
 */
final class RemoveTypedPropertyDeadInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
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
    public function __construct(IfManipulator $ifManipulator, ConstructorAssignDetector $constructorAssignDetector, PromotedPropertyResolver $promotedPropertyResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted property', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $someObject;

    public function __construct(SomeObject $someObject)
    {
        $this->someObject = $someObject;
    }

    public function run()
    {
        if ($this->someObject instanceof SomeObject) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $someObject;

    public function __construct(SomeObject $someObject)
    {
        $this->someObject = $someObject;
    }

    public function run()
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        $hasChanged = \false;
        $class = $node;
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $node) use(&$hasChanged, $class) {
            // avoid loop ifs
            if ($node instanceof While_ || $node instanceof Foreach_ || $node instanceof For_ || $node instanceof Do_) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if (!$node instanceof If_) {
                return null;
            }
            if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
                return null;
            }
            if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
                $result = $this->refactorStmtAndInstanceof($class, $node, $node->cond->expr);
                if ($result !== null) {
                    $hasChanged = \true;
                    return $result;
                }
            }
            if ($node->cond instanceof Instanceof_) {
                $result = $this->refactorStmtAndInstanceof($class, $node, $node->cond);
                if ($result !== null) {
                    $hasChanged = \true;
                    return $result;
                }
            }
            return null;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorStmtAndInstanceof(Class_ $class, If_ $if, Instanceof_ $instanceof)
    {
        // check local property only
        if (!$instanceof->expr instanceof PropertyFetch || !$this->isName($instanceof->expr->var, 'this')) {
            return null;
        }
        if (!$instanceof->class instanceof Name) {
            return null;
        }
        $classType = $this->nodeTypeResolver->getType($instanceof->class);
        $exprType = $this->nodeTypeResolver->getType($instanceof->expr);
        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)->yes();
        if (!$isSameStaticTypeOrSubtype) {
            return null;
        }
        if (!$this->isInPropertyPromotedParams($class, $instanceof->expr) && $this->isSkippedPropertyFetch($class, $instanceof->expr)) {
            return null;
        }
        if ($if->cond !== $instanceof) {
            return NodeTraverser::REMOVE_NODE;
        }
        if ($if->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return $if->stmts;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     */
    private function isSkippedPropertyFetch(Class_ $class, $propertyFetch) : bool
    {
        $propertyName = $this->getName($propertyFetch->name);
        if ($propertyName === null) {
            return \true;
        }
        if ($this->constructorAssignDetector->isPropertyAssigned($class, $propertyName)) {
            return \false;
        }
        $property = $class->getProperty($propertyName);
        if (!$property instanceof Property) {
            return \true;
        }
        return $property->type === null;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
     */
    private function isInPropertyPromotedParams(Class_ $class, $propertyFetch) : bool
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        $params = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return \true;
            }
        }
        return \false;
    }
}
