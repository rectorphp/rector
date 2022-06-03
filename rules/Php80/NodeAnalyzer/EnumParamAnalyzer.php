<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ParameterReflection;
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

    public function matchParameterClassName(ParameterReflection $parameterReflection, PhpDocInfo $phpDocInfo): ?string
    {
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
        if (! $paramTagValueNode instanceof ParamTagValueNode) {
            return null;
        }

        $className = $this->resolveClassFromConstType($paramTagValueNode->type);
        if ($className === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        return $className;
    }

    public function matchReturnClassName(PhpDocInfo $phpDocInfo): ?string
    {
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }

        return $this->resolveClassFromConstType($returnTagValueNode->type);
    }

    private function resolveClassFromConstType(TypeNode $typeNode): ?string
    {
        if (! $typeNode instanceof ConstTypeNode) {
            return null;
        }

        if (! $typeNode->constExpr instanceof ConstFetchNode) {
            return null;
        }

        $constExpr = $typeNode->constExpr;
        return $constExpr->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);
    }
}
