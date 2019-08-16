<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\ReturnTypeResolver\ReturnTypeResolver;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class GetterOrSetterPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var ReturnTypeResolver
     */
    private $returnTypeResolver;

    public function __construct(ReturnTypeResolver $returnTypeResolver)
    {
        $this->returnTypeResolver = $returnTypeResolver;
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

            $returnTypes = $this->resolveClassMethodReturnTypes($classMethod);
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
     * @return string[]
     */
    private function resolveClassMethodReturnTypes(ClassMethod $classMethod): array
    {
        $returnType = $this->returnTypeResolver->resolveFunctionLikeReturnType($classMethod);
        if ($returnType === null) {
            return [];
        }

        return $returnType->getDocTypes();
    }
}
