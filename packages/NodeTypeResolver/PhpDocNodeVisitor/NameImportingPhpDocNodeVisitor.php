<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PhpParser\Node as PhpParserNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class NameImportingPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    private ?PhpParserNode $currentPhpParserNode = null;

    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private ParameterProvider $parameterProvider,
        private ClassNameImportSkipper $classNameImportSkipper,
        private UseNodesToAddCollector $useNodesToAddCollector,
        private CurrentFileProvider $currentFileProvider
    ) {
    }

    public function beforeTraverse(Node $node): void
    {
        if ($this->currentPhpParserNode === null) {
            throw new ShouldNotHappenException('Set "$currentPhpParserNode" first');
        }
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof SpacelessPhpDocTagNode) {
            return $this->enterSpacelessPhpDocTagNode($node);
        }

        if ($node instanceof DoctrineAnnotationTagValueNode) {
            $this->processDoctrineAnnotationTagValueNode($node);
            return $node;
        }

        if (! $node instanceof IdentifierTypeNode) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $node,
            $this->currentPhpParserNode
        );
        if (! $staticType instanceof FullyQualifiedObjectType) {
            return null;
        }

        // Importing root namespace classes (like \DateTime) is optional
        if ($this->shouldSkipShortClassName($staticType)) {
            return null;
        }

        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            return null;
        }

        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType, $file);
    }

    public function setCurrentNode(PhpParserNode $phpParserNode): void
    {
        $this->currentPhpParserNode = $phpParserNode;
    }

    private function processFqnNameImport(
        PhpParserNode $phpParserNode,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType,
        File $file
    ): ?IdentifierTypeNode {
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $file,
            $phpParserNode,
            $fullyQualifiedObjectType
        )) {
            return null;
        }

        $parent = $identifierTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if ($parent instanceof TemplateTagValueNode) {
            // might break
            return null;
        }

        $newNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());

        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType)) {
            if (! $this->useNodesToAddCollector->isImportShortable($file, $fullyQualifiedObjectType)) {
                return null;
            }

            if ($newNode->name !== $identifierTypeNode->name) {
                $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
                return $newNode;
            }

            return null;
        }

        if ($newNode->name !== $identifierTypeNode->name) {
            // do not import twice
            if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType)) {
                return null;
            }

            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
            return $newNode;
        }

        return null;
    }

    private function shouldSkipShortClassName(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $importShortClasses = $this->parameterProvider->provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        if ($importShortClasses) {
            return false;
        }

        return substr_count($fullyQualifiedObjectType->getClassName(), '\\') === 0;
    }

    private function processDoctrineAnnotationTagValueNode(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): void {
        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $identifierTypeNode,
            $this->currentPhpParserNode
        );

        if (! $staticType instanceof FullyQualifiedObjectType) {
            if (! $staticType instanceof ObjectType) {
                return;
            }

            $staticType = new FullyQualifiedObjectType($staticType->getClassName());
        }

        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            return;
        }

        $shortentedIdentifierTypeNode = $this->processFqnNameImport(
            $this->currentPhpParserNode,
            $identifierTypeNode,
            $staticType,
            $file
        );

        if (! $shortentedIdentifierTypeNode instanceof IdentifierTypeNode) {
            return;
        }

        $doctrineAnnotationTagValueNode->identifierTypeNode = $shortentedIdentifierTypeNode;
        $doctrineAnnotationTagValueNode->markAsChanged();
    }

    private function enterSpacelessPhpDocTagNode(
        SpacelessPhpDocTagNode $spacelessPhpDocTagNode
    ): SpacelessPhpDocTagNode | null {
        if (! $spacelessPhpDocTagNode->value instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }

        // special case for doctrine annotation
        if (! str_starts_with($spacelessPhpDocTagNode->name, '@')) {
            return null;
        }

        $attributeClass = ltrim($spacelessPhpDocTagNode->name, '@\\');
        $identifierTypeNode = new IdentifierTypeNode($attributeClass);

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            new IdentifierTypeNode($attributeClass),
            $this->currentPhpParserNode
        );

        if (! $staticType instanceof FullyQualifiedObjectType) {
            if (! $staticType instanceof ObjectType) {
                return null;
            }

            $staticType = new FullyQualifiedObjectType($staticType->getClassName());
        }

        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            return null;
        }

        $importedName = $this->processFqnNameImport(
            $this->currentPhpParserNode,
            $identifierTypeNode,
            $staticType,
            $file
        );

        if ($importedName !== null) {
            $spacelessPhpDocTagNode->name = '@' . $importedName->name;
            return $spacelessPhpDocTagNode;
        }

        return null;
    }
}
