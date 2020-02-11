<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeDeclarationToStringConverter;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class ReturnTypeDeclarationReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var TypeDeclarationToStringConverter
     */
    private $typeDeclarationToStringConverter;

    public function __construct(TypeDeclarationToStringConverter $typeDeclarationToStringConverter)
    {
        $this->typeDeclarationToStringConverter = $typeDeclarationToStringConverter;
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

        return $this->typeDeclarationToStringConverter->resolveFunctionLikeReturnTypeToPHPStanType($functionLike);
    }

    public function getPriority(): int
    {
        return 2000;
    }
}
