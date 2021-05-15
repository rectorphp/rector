<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class PropertyTypeManipulator
{
    /**
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer
     */
    private $docBlockClassRenamer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer $docBlockClassRenamer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function changePropertyType(\PhpParser\Node\Stmt\Property $property, string $oldClass, string $newClass) : void
    {
        if ($property->type !== null) {
            // fix later
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $oldToNewTypes = [new \Rector\NodeTypeResolver\ValueObject\OldToNewType(new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($oldClass), new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($newClass))];
        $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldToNewTypes);
        if ($phpDocInfo->hasChanged()) {
            // invoke phpdoc reprint
            $property->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        }
    }
}
