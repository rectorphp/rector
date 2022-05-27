<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
final class ReturnTypeDeclarationReturnTypeInfererTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\TypeDeclaration\FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        if ($functionLike->getReturnType() === null) {
            return new \PHPStan\Type\MixedType();
        }
        // resolve later with more precise type, e.g. Type[]
        if ($this->nodeNameResolver->isNames($functionLike->getReturnType(), ['array', 'iterable'])) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($functionLike);
    }
    public function getPriority() : int
    {
        return 2000;
    }
}
