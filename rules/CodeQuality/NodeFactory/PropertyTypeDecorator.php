<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
final class PropertyTypeDecorator
{
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private TypeNormalizer $typeNormalizer;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocInfoFactory $phpDocInfoFactory, TypeNormalizer $typeNormalizer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeNormalizer = $typeNormalizer;
    }
    public function decorateProperty(Property $property, Type $propertyType) : void
    {
        // generalize false/true type to bool, as mostly default value but accepts both
        $propertyType = $this->typeNormalizer->generalizeConstantBoolTypes($propertyType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->makeMultiLined();
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $propertyType);
    }
}
