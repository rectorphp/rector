<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
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

    /**
     * @var FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;

    /**
     * @var ClassMethodAndPropertyAnalyzer
     */
    private $classMethodAndPropertyAnalyzer;

    public function __construct(
        ReturnTagReturnTypeInferer $returnTagReturnTypeInferer,
        ReturnedNodesReturnTypeInferer $returnedNodesReturnTypeInferer,
        FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver,
        ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer
    ) {
        $this->returnedNodesReturnTypeInferer = $returnedNodesReturnTypeInferer;
        $this->returnTagReturnTypeInferer = $returnTagReturnTypeInferer;
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
    }

    public function inferProperty(Property $property): Type
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            // anonymous class
            return new MixedType();
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

        return new MixedType();
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
