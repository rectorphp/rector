<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
final class GetterTypeDeclarationPropertyTypeInferer
{
    /**
     * @readonly
     */
    private FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver;
    /**
     * @readonly
     */
    private ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver, ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer, NodeNameResolver $nodeNameResolver)
    {
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function inferProperty(Property $property, Class_ $class) : ?Type
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($class->getMethods() as $classMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasPropertyFetchReturn($classMethod, $propertyName)) {
                continue;
            }
            $returnType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($classMethod);
            // let PhpDoc solve that later for more precise type
            if ($returnType->isArray()->yes()) {
                return new MixedType();
            }
            if (!$returnType instanceof MixedType) {
                return $returnType;
            }
        }
        return null;
    }
}
