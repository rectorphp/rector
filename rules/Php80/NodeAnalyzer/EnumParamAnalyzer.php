<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function matchParameterClassName(\PHPStan\Reflection\ParameterReflection $parameterReflection, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?string
    {
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
        if (!$paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
            return null;
        }
        $className = $this->resolveClassFromConstType($paramTagValueNode->type);
        if ($className === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $className;
    }
    public function matchReturnClassName(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?string
    {
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode) {
            return null;
        }
        return $this->resolveClassFromConstType($returnTagValueNode->type);
    }
    private function resolveClassFromConstType(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode) : ?string
    {
        if (!$typeNode instanceof \PHPStan\PhpDocParser\Ast\Type\ConstTypeNode) {
            return null;
        }
        if (!$typeNode->constExpr instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode) {
            return null;
        }
        $constExpr = $typeNode->constExpr;
        return $constExpr->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS);
    }
}
