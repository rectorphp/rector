<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
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
    public function __construct(\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer $unionTypeAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name $typeNode
     */
    public function decoratePropertyUnionType(\PHPStan\Type\UnionType $unionType, $typeNode, \PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        if ($this->unionTypeAnalyzer->isNullable($unionType)) {
            $property->type = $typeNode;
            $propertyProperty = $property->props[0];
            // add null default
            if ($propertyProperty->default === null) {
                $propertyProperty->default = $this->nodeFactory->createNull();
            }
            return;
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES)) {
            $property->type = $typeNode;
            return;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $unionType);
    }
}
