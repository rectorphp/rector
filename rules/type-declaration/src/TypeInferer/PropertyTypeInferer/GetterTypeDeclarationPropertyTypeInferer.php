<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class GetterTypeDeclarationPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;

    /**
     * @var ClassMethodAndPropertyAnalyzer
     */
    private $classMethodAndPropertyAnalyzer;

    public function __construct(FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver,
        ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer
    ) {
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

            $returnType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType(
                $classMethod
            );
            // let PhpDoc solve that later for more precise type
            if ($returnType instanceof ArrayType) {
                return new MixedType();
            }

            if (! $returnType instanceof MixedType) {
                return $returnType;
            }
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 630;
    }
}
