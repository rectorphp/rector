<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\NodeTypeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\PHPStan\Type\VerbosityLevel;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use RectorPrefix20220606\Rector\Core\Php\PhpVersionProvider;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
final class PropertyTypeDecorator
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(UnionTypeAnalyzer $unionTypeAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, PhpVersionProvider $phpVersionProvider, NodeFactory $nodeFactory)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType $typeNode
     */
    public function decoratePropertyUnionType(UnionType $unionType, $typeNode, Property $property, PhpDocInfo $phpDocInfo) : void
    {
        if (!$this->unionTypeAnalyzer->isNullable($unionType)) {
            if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
                $property->type = $typeNode;
            } else {
                $this->phpDocTypeChanger->changeVarType($phpDocInfo, $unionType);
            }
            return;
        }
        $property->type = $typeNode;
        $propertyProperty = $property->props[0];
        // add null default
        if ($propertyProperty->default === null) {
            $propertyProperty->default = $this->nodeFactory->createNull();
        }
        // has array with defined type? add docs
        if ($this->isDocBlockRequired($unionType)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $unionType);
        }
    }
    private function isDocBlockRequired(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ArrayType) {
                $describedArray = $unionedType->describe(VerbosityLevel::value());
                if ($describedArray !== 'array') {
                    return \true;
                }
            }
        }
        return \false;
    }
}
