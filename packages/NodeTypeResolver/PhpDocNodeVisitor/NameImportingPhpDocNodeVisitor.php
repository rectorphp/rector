<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PhpParser\Node as PhpParserNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
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
     * @var PhpParserNode|null
     */
    private $currentPhpParserNode;

    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private ParameterProvider $parameterProvider,
        private ClassNameImportSkipper $classNameImportSkipper,
        private UseNodesToAddCollector $useNodesToAddCollector
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

        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType);
    }

    public function setCurrentNode(PhpParserNode $phpParserNode): void
    {
        $this->currentPhpParserNode = $phpParserNode;
    }

    private function processFqnNameImport(
        PhpParserNode $phpParserNode,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): ?IdentifierTypeNode {
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
            $phpParserNode,
            $fullyQualifiedObjectType
        )) {
            return $identifierTypeNode;
        }

        $parent = $identifierTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if ($parent instanceof TemplateTagValueNode) {
            // might break
            return null;
        }

        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($phpParserNode, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($phpParserNode, $fullyQualifiedObjectType)) {
                $newNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
                if ($newNode->name !== $identifierTypeNode->name) {
                    return $newNode;
                }
                return $identifierTypeNode;
            }

            return $identifierTypeNode;
        }

        $this->useNodesToAddCollector->addUseImport($phpParserNode, $fullyQualifiedObjectType);

        $newNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
        if ($newNode->name !== $identifierTypeNode->name) {
            return $newNode;
        }
        return $identifierTypeNode;
    }

    private function shouldSkipShortClassName(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $importShortClasses = $this->parameterProvider->provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        if ($importShortClasses) {
            return false;
        }

        return substr_count($fullyQualifiedObjectType->getClassName(), '\\') === 0;
    }
}
