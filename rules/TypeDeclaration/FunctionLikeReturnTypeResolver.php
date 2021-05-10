<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class FunctionLikeReturnTypeResolver
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    public function resolveFunctionLikeReturnTypeToPHPStanType(FunctionLike $functionLike): Type
    {
        $functionReturnType = $functionLike->getReturnType();
        if ($functionReturnType === null) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionReturnType);
    }
}
