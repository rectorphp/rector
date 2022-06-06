<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantStringType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
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
    public function __construct(PHPStanStaticTypeMapper $phpStanStaticTypeMapper, ReflectionProvider $reflectionProvider)
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode|\PHPStan\Type\ArrayType|null
     */
    public function mapConstantArrayType(ConstantArrayType $constantArrayType)
    {
        // empty array
        if ($constantArrayType->getKeyType() instanceof NeverType) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        $arrayShapeItemNodes = [];
        foreach ($constantArrayType->getKeyTypes() as $index => $keyType) {
            // not real array shape
            if (!$keyType instanceof ConstantStringType) {
                return null;
            }
            $keyValue = $keyType->getValue();
            if ($this->reflectionProvider->hasClass($keyValue)) {
                return null;
            }
            $keyDocTypeNode = new IdentifierTypeNode($keyValue);
            $valueType = $constantArrayType->getValueTypes()[$index];
            $valueDocTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($valueType, TypeKind::RETURN);
            $arrayShapeItemNodes[] = new ArrayShapeItemNode($keyDocTypeNode, $constantArrayType->isOptionalKey($index), $valueDocTypeNode);
        }
        return new ArrayShapeNode($arrayShapeItemNodes);
    }
}
