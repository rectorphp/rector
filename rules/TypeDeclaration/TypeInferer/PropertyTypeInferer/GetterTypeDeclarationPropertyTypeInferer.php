<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
final class GetterTypeDeclarationPropertyTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface
{
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
    public function __construct(\Rector\TypeDeclaration\FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver, \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property $property
     */
    public function inferProperty($property) : ?\PHPStan\Type\Type
    {
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            // anonymous class
            return null;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($classLike->getMethods() as $classMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasClassMethodOnlyStatementReturnOfPropertyFetch($classMethod, $propertyName)) {
                continue;
            }
            $returnType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($classMethod);
            // let PhpDoc solve that later for more precise type
            if ($returnType instanceof \PHPStan\Type\ArrayType) {
                return new \PHPStan\Type\MixedType();
            }
            if (!$returnType instanceof \PHPStan\Type\MixedType) {
                return $returnType;
            }
        }
        return null;
    }
    public function getPriority() : int
    {
        return 630;
    }
}
