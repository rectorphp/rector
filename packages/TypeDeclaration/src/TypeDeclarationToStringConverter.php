<?php declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class TypeDeclarationToStringConverter
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * @return string[]
     */
    public function resolveFunctionLikeReturnTypeToString(FunctionLike $functionLike): array
    {
        if ($functionLike->getReturnType() === null) {
            return [];
        }

        $returnType = $functionLike->getReturnType();

        $types = [];

        $type = $returnType instanceof NullableType ? $returnType->type : $returnType;
        $result = $this->nameResolver->getName($type);
        if ($result !== null) {
            $types[] = $result;
        }

        if ($returnType instanceof NullableType) {
            $types[] = 'null';
        }

        return $types;
    }
}
