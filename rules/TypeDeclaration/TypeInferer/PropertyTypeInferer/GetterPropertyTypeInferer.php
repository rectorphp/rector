<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTagReturnTypeInferer;

final class GetterPropertyTypeInferer implements PropertyTypeInfererInterface
{
    public function __construct(
        private ReturnTagReturnTypeInferer $returnTagReturnTypeInferer,
        private ReturnedNodesReturnTypeInferer $returnedNodesReturnTypeInferer,
        private FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver,
        private ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            // anonymous class
            return null;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        foreach ($classLike->getMethods() as $classMethod) {
            if (! $this->classMethodAndPropertyAnalyzer->hasClassMethodOnlyStatementReturnOfPropertyFetch(
                $classMethod,
                $propertyName
            )) {
                continue;
            }

            $returnType = $this->inferClassMethodReturnType($classMethod);

            if (! $returnType instanceof MixedType) {
                return $returnType;
            }
        }

        return null;
    }

    public function getPriority(): int
    {
        return 1700;
    }

    private function inferClassMethodReturnType(ClassMethod $classMethod): Type
    {
        $returnTypeDeclarationType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType(
            $classMethod
        );

        if (! $returnTypeDeclarationType instanceof MixedType) {
            return $returnTypeDeclarationType;
        }

        $inferedType = $this->returnedNodesReturnTypeInferer->inferFunctionLike($classMethod);
        if (! $inferedType instanceof MixedType) {
            return $inferedType;
        }

        return $this->returnTagReturnTypeInferer->inferFunctionLike($classMethod);
    }
}
