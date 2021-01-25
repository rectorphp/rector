<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class PropertyTypeManipulator
{
    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(DocBlockClassRenamer $docBlockClassRenamer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function changePropertyType(Property $property, string $oldClass, string $newClass): void
    {
        if ($property->type !== null) {
            // fix later
            throw new NotImplementedYetException();
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->docBlockClassRenamer->renamePhpDocType(
            $phpDocInfo,
            new FullyQualifiedObjectType($oldClass),
            new FullyQualifiedObjectType($newClass),
            $property
        );
    }
}
