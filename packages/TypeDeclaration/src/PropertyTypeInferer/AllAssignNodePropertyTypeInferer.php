<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class AllAssignNodePropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferProperty(Node\Stmt\Property $property): array
    {
        /** @var ClassLike $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $propertyName = $this->nameResolver->getName($property);

        $assignedExprStaticTypes = $this->collectAllPropertyAsignExprStaticTypes($class, $propertyName);
        if ($assignedExprStaticTypes === []) {
            return [];
        }

        $assignedExprStaticType = new IntersectionType($assignedExprStaticTypes);

        return $this->staticTypeToStringResolver->resolveObjectType($assignedExprStaticType);
    }

    public function getPriority(): int
    {
        return 500;
    }

    /**
     * @return Type[]
     */
    private function collectAllPropertyAsignExprStaticTypes(ClassLike $classLike, string $propertyName): array
    {
        $assignedExprStaticTypes = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($classLike->stmts, function (Node $node) use (
            $propertyName,
            &$assignedExprStaticTypes
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            $expr = $this->matchPropertyAssignExpr($node, $propertyName);
            if ($expr === null) {
                return null;
            }

            $exprStaticType = $this->nodeTypeResolver->getNodeStaticType($node->expr);
            if ($exprStaticType === null) {
                return null;
            }

            if ($exprStaticType instanceof ErrorType) {
                return null;
            }

            if ($node->var instanceof ArrayDimFetch) {
                $exprStaticType = new ArrayType(new MixedType(), $exprStaticType);
            }

            $assignedExprStaticTypes[] = $exprStaticType;

            return null;
        });

        return $this->filterOutDuplicatedTypes($assignedExprStaticTypes);
    }

    /**
     * Covers:
     * - $this->propertyName = $expr;
     * - $this->propertyName[] = $expr;
     */
    private function matchPropertyAssignExpr(Assign $assign, string $propertyName): ?Node\Expr
    {
        if ($assign->var instanceof PropertyFetch) {
            if (! $this->nameResolver->isName($assign->var, $propertyName)) {
                return null;
            }

            return $assign->expr;
        }

        if ($assign->var instanceof ArrayDimFetch && $assign->var->var instanceof PropertyFetch) {
            if (! $this->nameResolver->isName($assign->var->var, $propertyName)) {
                return null;
            }

            return $assign->expr;
        }

        return null;
    }

    /**
     * @param Type[] $types
     * @return Type[]
     */
    private function filterOutDuplicatedTypes(array $types): array
    {
        if (count($types) === 1) {
            return $types;
        }

        $uniqueTypes = [];
        foreach ($types as $type) {
            $valueObjectHash = md5(serialize($type));
            $uniqueTypes[$valueObjectHash] = $type;
        }

        return $uniqueTypes;
    }
}
