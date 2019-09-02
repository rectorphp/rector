<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTagReturnTypeInferer;

final class GetterPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var ReturnedNodesReturnTypeInferer
     */
    private $returnedNodesReturnTypeInferer;

    /**
     * @var ReturnTagReturnTypeInferer
     */
    private $returnTagReturnTypeInferer;

    public function __construct(
        ReturnedNodesReturnTypeInferer $returnedNodesReturnTypeInferer,
        ReturnTagReturnTypeInferer $returnTagReturnTypeInferer
    ) {
        $this->returnedNodesReturnTypeInferer = $returnedNodesReturnTypeInferer;
        $this->returnTagReturnTypeInferer = $returnTagReturnTypeInferer;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        /** @var string $propertyName */
        $propertyName = $this->nameResolver->getName($property);

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->hasClassMethodOnlyStatementReturnOfPropertyFetch($classMethod, $propertyName)) {
                continue;
            }

            $returnTypes = $this->inferClassMethodReturnTypes($classMethod);
            if ($returnTypes !== []) {
                return $returnTypes;
            }
        }

        return [];
    }

    public function getPriority(): int
    {
        return 600;
    }

    private function hasClassMethodOnlyStatementReturnOfPropertyFetch(
        ClassMethod $classMethod,
        string $propertyName
    ): bool {
        if (count((array) $classMethod->stmts) !== 1) {
            return false;
        }

        $onlyClassMethodStmt = $classMethod->stmts[0];
        if (! $onlyClassMethodStmt instanceof Return_) {
            return false;
        }

        /** @var Return_ $return */
        $return = $onlyClassMethodStmt;

        if (! $return->expr instanceof PropertyFetch) {
            return false;
        }

        return $this->nameResolver->isName($return->expr, $propertyName);
    }

    /**
     * Intentionally local method ↓
     * @todo possible move to ReturnTypeInferer, but allow to disable/enable in case of override returnType (99 %)
     *
     * @param ClassMethod|Function_|Closure $functionLike
     * @return string[]
     */
    private function resolveFunctionLikeReturnTypeDeclaration(FunctionLike $functionLike): array
    {
        if ($functionLike->returnType === null) {
            return [];
        }

        return $this->resolveReturnTypeToString($functionLike->returnType);
    }

    /**
     * @return string[]
     */
    private function inferClassMethodReturnTypes(ClassMethod $classMethod): array
    {
        $returnTypeDeclarationTypes = $this->resolveFunctionLikeReturnTypeDeclaration($classMethod);
        if ($returnTypeDeclarationTypes) {
            return $returnTypeDeclarationTypes;
        }

        $inferedTypes = $this->returnedNodesReturnTypeInferer->inferFunctionLike($classMethod);
        if ($inferedTypes) {
            return $inferedTypes;
        }

        return $this->returnTagReturnTypeInferer->inferFunctionLike($classMethod);
    }

    /**
     * @param Identifier|Name|NullableType $node
     * @return string[]
     */
    private function resolveReturnTypeToString(Node $node): array
    {
        $types = [];

        $type = $node instanceof NullableType ? $node->type : $node;
        $result = $this->nameResolver->getName($type);
        if ($result !== null) {
            $types[] = $result;
        }

        if ($node instanceof NullableType) {
            $types[] = 'null';
        }

        return $types;
    }
}
