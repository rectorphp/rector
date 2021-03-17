<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;

final class ReturnTypeDeclarationReturnTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        if ($functionLike->getReturnType() === null) {
            return new MixedType();
        }

        // resolve later with more precise type, e.g. Type[]
        if ($this->nodeNameResolver->isNames($functionLike->getReturnType(), ['array', 'iterable'])) {
            return new MixedType();
        }

        return $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($functionLike);
    }

    public function getPriority(): int
    {
        return 2000;
    }
}
