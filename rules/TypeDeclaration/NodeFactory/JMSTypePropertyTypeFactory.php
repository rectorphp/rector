<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\FloatType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class JMSTypePropertyTypeFactory
{
    /**
     * @readonly
     */
    private ScalarStringToTypeMapper $scalarStringToTypeMapper;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private VarTagRemover $varTagRemover;
    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, VarTagRemover $varTagRemover)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->varTagRemover = $varTagRemover;
    }
    public function createObjectTypeNode(string $typeValue): ?Node
    {
        // skip generic iterable types
        if (strpos($typeValue, '<') !== \false) {
            return null;
        }
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
        if ($type instanceof MixedType) {
            // fallback to object type
            $type = new ObjectType($typeValue);
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
    }
    public function createScalarTypeNode(string $typeValue, Property $property): ?Node
    {
        if ($typeValue === 'float') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof StringType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('string');
            }
        }
        if ($typeValue === 'string') {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            // fallback to string, as most likely string representation of float
            if ($propertyPhpDocInfo instanceof PhpDocInfo && $propertyPhpDocInfo->getVarType() instanceof FloatType) {
                $this->varTagRemover->removeVarTag($property);
                return new Identifier('float');
            }
        }
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeValue);
        if ($type instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
    }
}
