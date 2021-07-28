<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    public function __construct(
        private IfManipulator $ifManipulator,
        private PropertyFetchAnalyzer $propertyFetchAnalyzer,
        private ConstructorAssignDetector $constructorAssignDetector,
        private PromotedPropertyResolver $promotedPropertyResolver,
        private PropertyAssignMatcher $propertyAssignMatcher
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        return true;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?If_
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        // a trait
        if (! $scope instanceof Scope) {
            return null;
        }

        if (! $this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }

        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond->expr);
        }

        if ($node->cond instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond);
        }

        return $node;
    }

    private function processMayDeadInstanceOf(If_ $if, Instanceof_ $instanceof): ?If_
    {
        $classType = $this->nodeTypeResolver->resolve($instanceof->class);
        $exprType = $this->nodeTypeResolver->resolve($instanceof->expr);

        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)
            ->yes();

        if (! $isSameStaticTypeOrSubtype) {
            return null;
        }

        if (! $instanceof->expr instanceof Variable && ! $this->isInPropertyPromotedParams(
            $instanceof->expr
        ) && $this->isSkippedPropertyFetch($instanceof->expr)) {
            return null;
        }

        if ($if->cond === $instanceof) {
            $this->addNodesBeforeNode($if->stmts, $if);
        }

        $this->removeNode($if);
        return $if;
    }

    private function isSkippedPropertyFetch(Expr $expr): bool
    {
        if (! $this->propertyAssignMatcher->isPropertyFetch($expr)) {
            return true;
        }

        /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
        $propertyFetch = $expr;
        $classLike = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);

        if (! $classLike instanceof Class_) {
            return true;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        $property = $classLike->getProperty($propertyName);

        if (! $property instanceof Property) {
            return true;
        }

        $isFilledByConstructParam = $this->propertyFetchAnalyzer->isFilledByConstructParam($property);
        if ($this->isInPropertyPromotedParams($propertyFetch)) {
            return false;
        }

        $isPropertyAssignedInConstuctor = $this->constructorAssignDetector->isPropertyAssigned(
            $classLike,
            $propertyName
        );

        return $property->type === null && ! $isPropertyAssignedInConstuctor && ! $isFilledByConstructParam;
    }

    private function isInPropertyPromotedParams(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        $classLike = $expr->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($expr);
        $params = $this->promotedPropertyResolver->resolveFromClass($classLike);

        foreach ($params as $param) {
            if ($this->nodeNameResolver->isName($param, $propertyName)) {
                return true;
            }
        }

        return false;
    }
}
