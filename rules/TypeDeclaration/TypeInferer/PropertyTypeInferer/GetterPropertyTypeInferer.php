<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
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
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            // anonymous class
            return null;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        foreach ($class->getMethods() as $classMethod) {
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
