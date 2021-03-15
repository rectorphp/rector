<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class DocBlockNameImporter
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ClassNameImportSkipper
     */
    private $classNameImportSkipper;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    public function __construct(
        ClassNameImportSkipper $classNameImportSkipper,
        ParameterProvider $parameterProvider,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->parameterProvider = $parameterProvider;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function importNames(PhpDocInfo $phpDocInfo, Node $phpParserNode): void
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        $this->phpDocNodeTraverser->traverseWithCallable($attributeAwarePhpDocNode, '', function (
            PhpDocParserNode $docNode
        ) use ($phpDocInfo, $phpParserNode): PhpDocParserNode {
            if (! $docNode instanceof IdentifierTypeNode) {
                return $docNode;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($docNode, $phpParserNode);
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return $docNode;
            }

            $importShortClasses = $this->parameterProvider->provideParameter(Option::IMPORT_SHORT_CLASSES);

            // Importing root namespace classes (like \DateTime) is optional
            if (! $importShortClasses && substr_count($staticType->getClassName(), '\\') === 0) {
                return $docNode;
            }

            return $this->processFqnNameImport($phpDocInfo, $phpParserNode, $docNode, $staticType);
        });
    }

    private function processFqnNameImport(
        PhpDocInfo $phpDocInfo,
        Node $node,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): PhpDocParserNode {
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $node,
            $fullyQualifiedObjectType
        )) {
            return $identifierTypeNode;
        }

        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($node, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($node, $fullyQualifiedObjectType)) {
                $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
                $phpDocInfo->markAsChanged();
            }

            return $identifierTypeNode;
        }

        $shortenedIdentifierTypeNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());

        $this->useNodesToAddCollector->addUseImport($node, $fullyQualifiedObjectType);
        $phpDocInfo->markAsChanged();

        return $shortenedIdentifierTypeNode;
    }
}
