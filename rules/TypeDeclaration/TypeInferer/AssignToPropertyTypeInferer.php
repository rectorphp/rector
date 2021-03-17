<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\AlreadyAssignDetector\NullTypeAssignDetector;
use Rector\TypeDeclaration\AlreadyAssignDetector\PropertyDefaultAssignDetector;
use Rector\TypeDeclaration\Matcher\PropertyAssignMatcher;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class AssignToPropertyTypeInferer
{
    /**
     * @var ConstructorAssignDetector
     */
    private $constructorAssignDetector;

    /**
     * @var PropertyAssignMatcher
     */
    private $propertyAssignMatcher;

    /**
     * @var PropertyDefaultAssignDetector
     */
    private $propertyDefaultAssignDetector;

    /**
     * @var NullTypeAssignDetector
     */
    private $nullTypeAssignDetector;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ConstructorAssignDetector $constructorAssignDetector,
        PropertyAssignMatcher $propertyAssignMatcher,
        PropertyDefaultAssignDetector $propertyDefaultAssignDetector,
        NullTypeAssignDetector $nullTypeAssignDetector,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        TypeFactory $typeFactory,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->propertyAssignMatcher = $propertyAssignMatcher;
        $this->propertyDefaultAssignDetector = $propertyDefaultAssignDetector;
        $this->nullTypeAssignDetector = $nullTypeAssignDetector;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function inferPropertyInClassLike(string $propertyName, ClassLike $classLike): Type
    {
        $assignedExprTypes = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike->stmts, function (Node $node) use (
            $propertyName,
            &$assignedExprTypes
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            $expr = $this->propertyAssignMatcher->matchPropertyAssignExpr($node, $propertyName);
            if (! $expr instanceof Expr) {
                return null;
            }

            $exprStaticType = $this->resolveExprStaticTypeIncludingDimFetch($node);
            if (! $exprStaticType instanceof Type) {
                return null;
            }

            $assignedExprTypes[] = $exprStaticType;

            return null;
        });

        if ($this->shouldAddNullType($classLike, $propertyName, $assignedExprTypes)) {
            $assignedExprTypes[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($assignedExprTypes);
    }

    private function resolveExprStaticTypeIncludingDimFetch(Assign $assign): ?Type
    {
        $exprStaticType = $this->nodeTypeResolver->getStaticType($assign->expr);
        if ($exprStaticType instanceof MixedType) {
            return null;
        }

        if ($assign->var instanceof ArrayDimFetch) {
            return new ArrayType(new MixedType(), $exprStaticType);
        }

        return $exprStaticType;
    }

    /**
     * @param Type[] $assignedExprTypes
     */
    private function shouldAddNullType(ClassLike $classLike, string $propertyName, array $assignedExprTypes): bool
    {
        $hasPropertyDefaultValue = $this->propertyDefaultAssignDetector->detect($classLike, $propertyName);
        $isAssignedInConstructor = $this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName);
        $shouldAddNullType = $this->nullTypeAssignDetector->detect($classLike, $propertyName);

        if (($assignedExprTypes === []) && ($isAssignedInConstructor || $hasPropertyDefaultValue)) {
            return false;
        }

        if ($shouldAddNullType === true) {
            if ($isAssignedInConstructor) {
                return false;
            }
            return ! $hasPropertyDefaultValue;
        }
        if ($assignedExprTypes === []) {
            return false;
        }
        if ($isAssignedInConstructor) {
            return false;
        }
        return ! $hasPropertyDefaultValue;
    }
}
