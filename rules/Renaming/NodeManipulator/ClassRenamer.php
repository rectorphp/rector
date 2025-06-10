<?php

declare (strict_types=1);
namespace Rector\Renaming\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocClassRenamer;
use Rector\BetterPhpDocParser\ValueObject\NodeTypes;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\Renaming\Collector\RenamedNameCollector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\Util\FileHasher;
final class ClassRenamer
{
    /**
     * @readonly
     */
    private PhpDocClassRenamer $phpDocClassRenamer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockClassRenamer $docBlockClassRenamer;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private FileHasher $fileHasher;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private RenamedNameCollector $renamedNameCollector;
    /**
     * @var array<string, OldToNewType[]>
     */
    private array $oldToNewTypesByCacheKey = [];
    public function __construct(PhpDocClassRenamer $phpDocClassRenamer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockClassRenamer $docBlockClassRenamer, ReflectionProvider $reflectionProvider, FileHasher $fileHasher, DocBlockUpdater $docBlockUpdater, RenamedNameCollector $renamedNameCollector)
    {
        $this->phpDocClassRenamer = $phpDocClassRenamer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->reflectionProvider = $reflectionProvider;
        $this->fileHasher = $fileHasher;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->renamedNameCollector = $renamedNameCollector;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     * @return ($node is FullyQualified ? FullyQualified : Node)
     */
    public function renameNode(Node $node, array $oldToNewClasses, ?Scope $scope) : ?Node
    {
        $oldToNewTypes = $this->createOldToNewTypes($oldToNewClasses);
        // execute FullyQualified before Name on purpose so next Name check is pure Name node
        if ($node instanceof FullyQualified) {
            return $this->refactorName($node, $oldToNewClasses);
        }
        // Name as parent of FullyQualified executed for fallback annotation to attribute rename to Name
        if ($node instanceof Name) {
            $phpAttributeName = $node->getAttribute(AttributeKey::PHP_ATTRIBUTE_NAME);
            if (\is_string($phpAttributeName)) {
                return $this->refactorName(new FullyQualified($phpAttributeName), $oldToNewClasses);
            }
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $hasPhpDocChanged = $this->refactorPhpDoc($node, $oldToNewTypes, $oldToNewClasses, $phpDocInfo);
            if ($hasPhpDocChanged) {
                return $node;
            }
        }
        if ($node instanceof ClassLike) {
            return $this->refactorClassLike($node, $oldToNewClasses, $scope);
        }
        return null;
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorPhpDoc(Node $node, array $oldToNewTypes, array $oldToNewClasses, PhpDocInfo $phpDocInfo) : bool
    {
        if (!$phpDocInfo->hasByTypes(NodeTypes::TYPE_AWARE_NODES) && !$phpDocInfo->hasByAnnotationClasses(NodeTypes::TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES)) {
            return \false;
        }
        if ($node instanceof AttributeGroup) {
            return \false;
        }
        $hasChanged = $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldToNewTypes, $node);
        $hasChanged = $this->phpDocClassRenamer->changeTypeInAnnotationTypes($node, $phpDocInfo, $oldToNewClasses, $hasChanged);
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            return \true;
        }
        return \false;
    }
    private function shouldSkip(string $newName, FullyQualified $fullyQualified) : bool
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_STATICCALL_CLASS_NAME) === \true && $this->reflectionProvider->hasClass($newName)) {
            $classReflection = $this->reflectionProvider->getClass($newName);
            return $classReflection->isInterface();
        }
        return \false;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorName(FullyQualified $fullyQualified, array $oldToNewClasses) : ?FullyQualified
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true) {
            return null;
        }
        $stringName = $fullyQualified->toString();
        $newName = $oldToNewClasses[$stringName] ?? null;
        if ($newName === null) {
            return null;
        }
        if (!$this->isClassToInterfaceValidChange($fullyQualified, $newName)) {
            return null;
        }
        if ($this->shouldSkip($newName, $fullyQualified)) {
            return null;
        }
        $this->renamedNameCollector->add($stringName);
        return new FullyQualified($newName);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorClassLike(ClassLike $classLike, array $oldToNewClasses, ?Scope $scope) : ?Node
    {
        // rename interfaces
        if (!$classLike instanceof Class_) {
            return null;
        }
        $hasChanged = \false;
        $classLike->implements = \array_unique($classLike->implements);
        foreach ($classLike->implements as $key => $implementName) {
            $namespaceName = $scope instanceof Scope ? $scope->getNamespace() : null;
            $fullyQualifiedName = $namespaceName . '\\' . $implementName->toString();
            $newName = $oldToNewClasses[$fullyQualifiedName] ?? null;
            if ($newName === null) {
                continue;
            }
            $classLike->implements[$key] = new FullyQualified($newName);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classLike;
        }
        return null;
    }
    /**
     * Checks validity:
     *
     * - extends SomeClass
     * - extends SomeInterface
     *
     * - new SomeClass
     * - new SomeInterface
     *
     * - implements SomeInterface
     * - implements SomeClass
     */
    private function isClassToInterfaceValidChange(FullyQualified $fullyQualified, string $newClassName) : bool
    {
        if (!$this->reflectionProvider->hasClass($newClassName)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($newClassName);
        // ensure new is not with interface
        if ($fullyQualified->getAttribute(AttributeKey::IS_NEW_INSTANCE_NAME) !== \true) {
            return $this->isValidClassNameChange($fullyQualified, $classReflection);
        }
        if (!$classReflection->isInterface()) {
            return $this->isValidClassNameChange($fullyQualified, $classReflection);
        }
        return \false;
    }
    private function isValidClassNameChange(FullyQualified $fullyQualified, ClassReflection $classReflection) : bool
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_CLASS_EXTENDS) === \true) {
            // is class to interface?
            if ($classReflection->isInterface()) {
                return \false;
            }
            if ($classReflection->isFinalByKeyword()) {
                return \false;
            }
        }
        if ($fullyQualified->getAttribute(AttributeKey::IS_CLASS_IMPLEMENT) === \true) {
            // is interface to class?
            return !$classReflection->isClass();
        }
        return \true;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     * @return OldToNewType[]
     */
    private function createOldToNewTypes(array $oldToNewClasses) : array
    {
        $serialized = \serialize($oldToNewClasses);
        $cacheKey = $this->fileHasher->hash($serialized);
        if (isset($this->oldToNewTypesByCacheKey[$cacheKey])) {
            return $this->oldToNewTypesByCacheKey[$cacheKey];
        }
        $oldToNewTypes = [];
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            $oldObjectType = new ObjectType($oldClass);
            $newObjectType = new FullyQualifiedObjectType($newClass);
            $oldToNewTypes[] = new OldToNewType($oldObjectType, $newObjectType);
        }
        $this->oldToNewTypesByCacheKey[$cacheKey] = $oldToNewTypes;
        return $oldToNewTypes;
    }
}
