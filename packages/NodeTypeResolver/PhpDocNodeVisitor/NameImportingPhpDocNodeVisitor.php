<?php

declare (strict_types=1);
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
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20210705\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class NameImportingPhpDocNodeVisitor extends \RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @var PhpParserNode|null
     */
    private $currentPhpParserNode;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20210705\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper $classNameImportSkipper, \Rector\PostRector\Collector\UseNodesToAddCollector $useNodesToAddCollector)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function beforeTraverse($node) : void
    {
        if ($this->currentPhpParserNode === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException('Set "$currentPhpParserNode" first');
        }
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function enterNode($node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        if ($node instanceof \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode) {
            return $this->enterSpacelessPhpDocTagNode($node);
        }
        if ($node instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            $this->processDoctrineAnnotationTagValueNode($node);
            return $node;
        }
        if (!$node instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $this->currentPhpParserNode);
        if (!$staticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            return null;
        }
        // Importing root namespace classes (like \DateTime) is optional
        if ($this->shouldSkipShortClassName($staticType)) {
            return null;
        }
        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType);
    }
    /**
     * @param PhpParserNode $phpParserNode
     */
    public function setCurrentNode($phpParserNode) : void
    {
        $this->currentPhpParserNode = $phpParserNode;
    }
    /**
     * @param PhpParserNode $phpParserNode
     * @param \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifierTypeNode
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    private function processFqnNameImport($phpParserNode, $identifierTypeNode, $fullyQualifiedObjectType) : ?\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode
    {
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($phpParserNode, $fullyQualifiedObjectType)) {
            return $identifierTypeNode;
        }
        $parent = $identifierTypeNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::PARENT);
        if ($parent instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode) {
            // might break
            return null;
        }
        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($phpParserNode, $fullyQualifiedObjectType)) {
            if ($this->useNodesToAddCollector->isImportShortable($phpParserNode, $fullyQualifiedObjectType)) {
                $newNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
                if ($newNode->name !== $identifierTypeNode->name) {
                    return $newNode;
                }
                return $identifierTypeNode;
            }
            return $identifierTypeNode;
        }
        $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
        $newNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
        if ($newNode->name !== $identifierTypeNode->name) {
            return $newNode;
        }
        return $identifierTypeNode;
    }
    /**
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     */
    private function shouldSkipShortClassName($fullyQualifiedObjectType) : bool
    {
        $importShortClasses = $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES);
        if ($importShortClasses) {
            return \false;
        }
        return \substr_count($fullyQualifiedObjectType->getClassName(), '\\') === 0;
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
     */
    private function processDoctrineAnnotationTagValueNode($doctrineAnnotationTagValueNode) : void
    {
        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $this->currentPhpParserNode);
        if (!$staticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            if (!$staticType instanceof \PHPStan\Type\ObjectType) {
                return;
            }
            $staticType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($staticType->getClassName());
        }
        $shortentedIdentifierTypeNode = $this->processFqnNameImport($this->currentPhpParserNode, $identifierTypeNode, $staticType);
        if (!$shortentedIdentifierTypeNode instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            return;
        }
        $doctrineAnnotationTagValueNode->identifierTypeNode = $shortentedIdentifierTypeNode;
        $doctrineAnnotationTagValueNode->markAsChanged();
    }
    /**
     * @return \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode|null
     * @param \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode $spacelessPhpDocTagNode
     */
    private function enterSpacelessPhpDocTagNode($spacelessPhpDocTagNode)
    {
        if (!$spacelessPhpDocTagNode->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        // special case for doctrine annotation
        if (\strncmp($spacelessPhpDocTagNode->name, '@', \strlen('@')) !== 0) {
            return null;
        }
        $attributeClass = \ltrim($spacelessPhpDocTagNode->name, '@\\');
        $identifierTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($attributeClass);
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($attributeClass), $this->currentPhpParserNode);
        if (!$staticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            if (!$staticType instanceof \PHPStan\Type\ObjectType) {
                return null;
            }
            $staticType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($staticType->getClassName());
        }
        $importedName = $this->processFqnNameImport($this->currentPhpParserNode, $identifierTypeNode, $staticType);
        if ($importedName !== null) {
            $spacelessPhpDocTagNode->name = '@' . $importedName->name;
            return $spacelessPhpDocTagNode;
        }
        return null;
    }
}
