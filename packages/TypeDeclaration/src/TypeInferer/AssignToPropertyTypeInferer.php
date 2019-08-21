<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

final class AssignToPropertyTypeInferer extends AbstractTypeInferer
{
    /**
     * @return Type[]
     */
    public function inferPropertyInClassLike(string $propertyName, ClassLike $classLike): array
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
    private function matchPropertyAssignExpr(Assign $assign, string $propertyName): ?Expr
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
