<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class FunctionLikeReturnTypeResolver
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function resolveFunctionLikeReturnTypeToPHPStanType(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        $functionReturnType = $functionLike->getReturnType();
        if ($functionReturnType === null) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionReturnType);
    }
}
