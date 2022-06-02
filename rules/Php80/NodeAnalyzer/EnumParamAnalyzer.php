<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;

/**
 * Detects enum-like params, e.g.
 * Direction::*
 */
final class EnumParamAnalyzer
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function matchClassName(ParameterReflection $parameterReflection, PhpDocInfo $phpDocInfo): ?string
    {
        if (! $parameterReflection instanceof PhpParameterReflection) {
            return null;
        }

        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
        if (! $paramTagValueNode instanceof ParamTagValueNode) {
            return null;
        }

        if (! $paramTagValueNode->type instanceof ConstTypeNode) {
            return null;
        }

        $constTypeNode = $paramTagValueNode->type;
        if (! $constTypeNode->constExpr instanceof ConstFetchNode) {
            return null;
        }

        $constExpr = $constTypeNode->constExpr;
        $className = $constExpr->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        return $className;
    }
}
