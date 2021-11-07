<?php

declare (strict_types=1);
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
final class GetterPropertyTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTagReturnTypeInferer
     */
    private $returnTagReturnTypeInferer;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInferer
     */
    private $returnedNodesReturnTypeInferer;
    /**
     * @var \Rector\TypeDeclaration\FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer
     */
    private $classMethodAndPropertyAnalyzer;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTagReturnTypeInferer $returnTagReturnTypeInferer, \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnedNodesReturnTypeInferer $returnedNodesReturnTypeInferer, \Rector\TypeDeclaration\FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver, \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->returnTagReturnTypeInferer = $returnTagReturnTypeInferer;
        $this->returnedNodesReturnTypeInferer = $returnedNodesReturnTypeInferer;
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property $property
     */
    public function inferProperty($property) : ?\PHPStan\Type\Type
    {
        $class = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        $returnTypes = [];
        $classAndTraitMethods = $this->resolveClassAndTraitMethods($class);
        foreach ($classAndTraitMethods as $classAndTraitMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasClassMethodOnlyStatementReturnOfPropertyFetch($classAndTraitMethod, $propertyName)) {
                continue;
            }
            $returnType = $this->inferClassMethodReturnType($classAndTraitMethod);
            if ($returnType instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            $returnTypes[] = $returnType;
        }
        if ($returnTypes === []) {
            return null;
        }
        if (\count($returnTypes) === 1) {
            return $returnTypes[0];
        }
        return new \PHPStan\Type\UnionType($returnTypes);
    }
    public function getPriority() : int
    {
        return 1700;
    }
    private function inferClassMethodReturnType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : \PHPStan\Type\Type
    {
        $returnTypeDeclarationType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($classMethod);
        if (!$returnTypeDeclarationType instanceof \PHPStan\Type\MixedType) {
            return $returnTypeDeclarationType;
        }
        $inferedType = $this->returnedNodesReturnTypeInferer->inferFunctionLike($classMethod);
        if (!$inferedType instanceof \PHPStan\Type\MixedType) {
            return $inferedType;
        }
        return $this->returnTagReturnTypeInferer->inferFunctionLike($classMethod);
    }
    /**
     * @return ClassMethod[]
     */
    private function resolveClassAndTraitMethods(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classAndTraitMethods = $class->getMethods();
        foreach ($class->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $traitName) {
                $trait = $this->astResolver->resolveClassFromName($traitName->toString());
                if (!$trait instanceof \PhpParser\Node\Stmt\Trait_) {
                    continue;
                }
                $classAndTraitMethods = \array_merge($classAndTraitMethods, $trait->getMethods());
            }
        }
        return $classAndTraitMethods;
    }
}
