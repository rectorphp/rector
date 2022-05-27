<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
final class ArrayShapeTypeMapper
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper $phpStanStaticTypeMapper, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode|\PHPStan\Type\ArrayType|null
     */
    public function mapConstantArrayType(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType)
    {
        // empty array
        if ($constantArrayType->getKeyType() instanceof \PHPStan\Type\NeverType) {
            return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        $arrayShapeItemNodes = [];
        foreach ($constantArrayType->getKeyTypes() as $index => $keyType) {
            // not real array shape
            if (!$keyType instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return null;
            }
            $keyValue = $keyType->getValue();
            if ($this->reflectionProvider->hasClass($keyValue)) {
                return null;
            }
            $keyDocTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($keyValue);
            $valueType = $constantArrayType->getValueTypes()[$index];
            $valueDocTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($valueType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
            $arrayShapeItemNodes[] = new \PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode($keyDocTypeNode, $constantArrayType->isOptionalKey($index), $valueDocTypeNode);
        }
        return new \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode($arrayShapeItemNodes);
    }
}
