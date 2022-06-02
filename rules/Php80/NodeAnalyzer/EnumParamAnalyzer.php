<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function matchClassName(\PHPStan\Reflection\ParameterReflection $parameterReflection, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?string
    {
        if (!$parameterReflection instanceof \PHPStan\Reflection\Php\PhpParameterReflection) {
            return null;
        }
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
        if (!$paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
            return null;
        }
        if (!$paramTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\ConstTypeNode) {
            return null;
        }
        $constTypeNode = $paramTagValueNode->type;
        if (!$constTypeNode->constExpr instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode) {
            return null;
        }
        $constExpr = $constTypeNode->constExpr;
        $className = $constExpr->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS);
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $className;
    }
}
