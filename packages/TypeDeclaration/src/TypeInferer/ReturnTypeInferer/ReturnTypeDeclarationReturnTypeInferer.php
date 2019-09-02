<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
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

    /**
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        if ($functionLike->getReturnType() === null) {
            return [];
        }

        // resolve later with more precise type, e.g. Type[]
        if ($this->nameResolver->isNames($functionLike->getReturnType(), ['array', 'iterable'])) {
            return [];
        }

        return $this->typeDeclarationToStringConverter->resolveFunctionLikeReturnTypeToString($functionLike);
    }

    public function getPriority(): int
    {
        return 2000;
    }
}
