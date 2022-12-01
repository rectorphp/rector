<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use RectorPrefix202212\Nette\Utils\Strings;
use PhpParser\Node as PhpParserNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class NameImportingPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var PhpParserNode|null
     */
    private $currentPhpParserNode;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(StaticTypeMapper $staticTypeMapper, ParameterProvider $parameterProvider, ClassNameImportSkipper $classNameImportSkipper, UseNodesToAddCollector $useNodesToAddCollector, CurrentFileProvider $currentFileProvider, ReflectionProvider $reflectionProvider)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->parameterProvider = $parameterProvider;
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->currentFileProvider = $currentFileProvider;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function beforeTraverse(Node $node) : void
    {
        if ($this->currentPhpParserNode === null) {
            throw new ShouldNotHappenException('Set "$currentPhpParserNode" first');
        }
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof SpacelessPhpDocTagNode) {
            return $this->enterSpacelessPhpDocTagNode($node);
        }
        if ($node instanceof DoctrineAnnotationTagValueNode) {
            $this->processDoctrineAnnotationTagValueNode($node);
            return $node;
        }
        if (!$node instanceof IdentifierTypeNode) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $this->currentPhpParserNode);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        // Importing root namespace classes (like \DateTime) is optional
        if ($this->shouldSkipShortClassName($staticType)) {
            return null;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        if ($this->currentPhpParserNode === null) {
            throw new ShouldNotHappenException();
        }
        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType, $file);
    }
    public function setCurrentNode(PhpParserNode $phpParserNode) : void
    {
        $this->currentPhpParserNode = $phpParserNode;
    }
    private function processFqnNameImport(PhpParserNode $phpParserNode, IdentifierTypeNode $identifierTypeNode, FullyQualifiedObjectType $fullyQualifiedObjectType, File $file) : ?IdentifierTypeNode
    {
        if (\strncmp($fullyQualifiedObjectType->getClassName(), '@', \strlen('@')) === 0) {
            $fullyQualifiedObjectType = new FullyQualifiedObjectType(\ltrim($fullyQualifiedObjectType->getClassName(), '@'));
        }
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $phpParserNode, $fullyQualifiedObjectType)) {
            return null;
        }
        $parentNode = $identifierTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if ($parentNode instanceof TemplateTagValueNode) {
            // might break
            return null;
        }
        $newNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType)) {
            if (!$this->useNodesToAddCollector->isImportShortable($file, $fullyQualifiedObjectType)) {
                return null;
            }
            if ($this->shouldImport($newNode, $identifierTypeNode, $fullyQualifiedObjectType)) {
                $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
                return $newNode;
            }
            return null;
        }
        if ($this->shouldImport($newNode, $identifierTypeNode, $fullyQualifiedObjectType)) {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
            return $newNode;
        }
        return null;
    }
    private function shouldImport(IdentifierTypeNode $newNode, IdentifierTypeNode $identifierTypeNode, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        if ($newNode->name === $identifierTypeNode->name) {
            return \false;
        }
        if (\strncmp($identifierTypeNode->name, '\\', \strlen('\\')) === 0) {
            return \true;
        }
        $className = $fullyQualifiedObjectType->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $firstPath = Strings::before($identifierTypeNode->name, '\\' . $newNode->name);
        if ($firstPath === null) {
            return \true;
        }
        if ($firstPath === '') {
            return \true;
        }
        $namespaceParts = \explode('\\', \ltrim($firstPath, '\\'));
        return \count($namespaceParts) > 1;
    }
    private function shouldSkipShortClassName(FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        $importShortClasses = $this->parameterProvider->provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        if ($importShortClasses) {
            return \false;
        }
        return \substr_count($fullyQualifiedObjectType->getClassName(), '\\') === 0;
    }
    private function processDoctrineAnnotationTagValueNode(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $currentPhpParserNode = $this->currentPhpParserNode;
        if (!$currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $currentPhpParserNode);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            if (!$staticType instanceof ObjectType) {
                return;
            }
            $staticType = new FullyQualifiedObjectType($staticType->getClassName());
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return;
        }
        $shortentedIdentifierTypeNode = $this->processFqnNameImport($currentPhpParserNode, $identifierTypeNode, $staticType, $file);
        if (!$shortentedIdentifierTypeNode instanceof IdentifierTypeNode) {
            return;
        }
        $doctrineAnnotationTagValueNode->identifierTypeNode = $shortentedIdentifierTypeNode;
        $doctrineAnnotationTagValueNode->markAsChanged();
    }
    private function enterSpacelessPhpDocTagNode(SpacelessPhpDocTagNode $spacelessPhpDocTagNode) : ?\Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode
    {
        if (!$spacelessPhpDocTagNode->value instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        // special case for doctrine annotation
        if (\strncmp($spacelessPhpDocTagNode->name, '@', \strlen('@')) !== 0) {
            return null;
        }
        $attributeClass = \ltrim($spacelessPhpDocTagNode->name, '@\\');
        $identifierTypeNode = new IdentifierTypeNode($attributeClass);
        $currentPhpParserNode = $this->currentPhpParserNode;
        if (!$currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(new IdentifierTypeNode($attributeClass), $currentPhpParserNode);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            if (!$staticType instanceof ObjectType) {
                return null;
            }
            $staticType = new FullyQualifiedObjectType($staticType->getClassName());
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        $importedName = $this->processFqnNameImport($currentPhpParserNode, $identifierTypeNode, $staticType, $file);
        if ($importedName !== null) {
            $spacelessPhpDocTagNode->name = '@' . $importedName->name;
            return $spacelessPhpDocTagNode;
        }
        return null;
    }
}
