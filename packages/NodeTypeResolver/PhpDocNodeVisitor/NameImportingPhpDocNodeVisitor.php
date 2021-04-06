<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class NameImportingPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var ClassNameImportSkipper
     */
    private $classNameImportSkipper;

    /**
     * @var UseNodesToAddCollector
     */
    private $useNodesToAddCollector;

    /**
     * @var \PhpParser\Node|null
     */
    private $currentPhpParserNode;

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        ParameterProvider $parameterProvider,
        ClassNameImportSkipper $classNameImportSkipper,
        UseNodesToAddCollector $useNodesToAddCollector
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }

    public function beforeTraverse(Node $node): void
    {
        if ($this->currentPhpParserNode === null) {
            throw new ShouldNotHappenException('Set "$currentPhpParserNode" first');
        }
    }

    public function enterNode(Node $node): ?Node
    {
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

        $importShortClasses = $this->parameterProvider->provideBoolParameter(Option::IMPORT_SHORT_CLASSES);

        // Importing root namespace classes (like \DateTime) is optional
        if (! $importShortClasses && substr_count($staticType->getClassName(), '\\') === 0) {
            return null;
        }

        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType);
    }

    public function setCurrentNode(\PhpParser\Node $phpParserNode): void
    {
        $this->currentPhpParserNode = $phpParserNode;
    }

    private function processFqnNameImport(
        \PhpParser\Node $node,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): IdentifierTypeNode {
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $node,
            $fullyQualifiedObjectType
        )) {
            return $identifierTypeNode;
        }

        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($node, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($node, $fullyQualifiedObjectType)) {
                return new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
            }

            return $identifierTypeNode;
        }

        $this->useNodesToAddCollector->addUseImport($node, $fullyQualifiedObjectType);

        return new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
    }
}
