<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class PropertyTypeManipulator
{
    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(DocBlockClassRenamer $docBlockClassRenamer)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    public function changePropertyType(Property $property, string $oldClass, string $newClass): void
    {
        if ($property->type !== null) {
            // fix later
            throw new NotImplementedYetException();
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $this->docBlockClassRenamer->renamePhpDocType(
            $phpDocInfo->getPhpDocNode(),
            new FullyQualifiedObjectType($oldClass),
            new FullyQualifiedObjectType($newClass),
            $property
        );
    }
}
