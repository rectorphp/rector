<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\AstResolver;
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
        private AstResolver $astResolver
    ) {
    }

    public function inferProperty(Property $property): ?Type
    {
        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        $returnTypes = [];

        $classAndTraitMethods = $this->resolveClassAndTraitMethods($class);

        foreach ($classAndTraitMethods as $classAndTraitMethod) {
            if (! $this->classMethodAndPropertyAnalyzer->hasClassMethodOnlyStatementReturnOfPropertyFetch(
                $classAndTraitMethod,
                $propertyName
            )) {
                continue;
            }

            $returnType = $this->inferClassMethodReturnType($classAndTraitMethod);
            if ($returnType instanceof MixedType) {
                continue;
            }

            $returnTypes[] = $returnType;
        }

        if ($returnTypes === []) {
            return null;
        }

        if (count($returnTypes) === 1) {
            return $returnTypes[0];
        }

        return new UnionType($returnTypes);
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

    /**
     * @return ClassMethod[]
     */
    private function resolveClassAndTraitMethods(Class_ $class): array
    {
        $classAndTraitMethods = $class->getMethods();

        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (! $trait instanceof Trait_) {
                    continue;
                }

                $classAndTraitMethods = array_merge($classAndTraitMethods, $trait->getMethods());
            }
        }

        return $classAndTraitMethods;
    }
}
