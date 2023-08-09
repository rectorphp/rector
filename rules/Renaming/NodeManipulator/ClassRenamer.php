<?php

declare (strict_types=1);
namespace Rector\Renaming\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocClassRenamer;
use Rector\BetterPhpDocParser\ValueObject\NodeTypes;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\FileHasher;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ClassRenamer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocClassRenamer
     */
    private $phpDocClassRenamer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer
     */
    private $docBlockClassRenamer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\Util\FileHasher
     */
    private $fileHasher;
    /**
     * @var string[]
     */
    private $alreadyProcessedClasses = [];
    /**
     * @var array<string, OldToNewType[]>
     */
    private $oldToNewTypesByCacheKey = [];
    public function __construct(BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ClassNaming $classNaming, NodeNameResolver $nodeNameResolver, PhpDocClassRenamer $phpDocClassRenamer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockClassRenamer $docBlockClassRenamer, ReflectionProvider $reflectionProvider, FileHasher $fileHasher)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocClassRenamer = $phpDocClassRenamer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->reflectionProvider = $reflectionProvider;
        $this->fileHasher = $fileHasher;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function renameNode(Node $node, array $oldToNewClasses, ?Scope $scope) : ?Node
    {
        $oldToNewTypes = $this->createOldToNewTypes($oldToNewClasses);
        if ($node instanceof Name) {
            return $this->refactorName($node, $oldToNewClasses);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->refactorPhpDoc($node, $oldToNewTypes, $oldToNewClasses, $phpDocInfo);
        if ($node instanceof Namespace_) {
            return $this->refactorNamespace($node, $oldToNewClasses);
        }
        if ($node instanceof ClassLike) {
            return $this->refactorClassLike($node, $oldToNewClasses, $scope);
        }
        if ($phpDocInfo->hasChanged()) {
            return $node;
        }
        return null;
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorPhpDoc(Node $node, array $oldToNewTypes, array $oldToNewClasses, PhpDocInfo $phpDocInfo) : void
    {
        if (!$phpDocInfo->hasByTypes(NodeTypes::TYPE_AWARE_NODES) && !$phpDocInfo->hasByAnnotationClasses(NodeTypes::TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES)) {
            return;
        }
        if ($node instanceof AttributeGroup) {
            return;
        }
        $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldToNewTypes);
        $this->phpDocClassRenamer->changeTypeInAnnotationTypes($node, $phpDocInfo, $oldToNewClasses);
    }
    private function shouldSkip(string $newName, Name $name) : bool
    {
        if ($name->getAttribute(AttributeKey::IS_STATICCALL_CLASS_NAME) === \true && $this->reflectionProvider->hasClass($newName)) {
            $classReflection = $this->reflectionProvider->getClass($newName);
            return $classReflection->isInterface();
        }
        return \false;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorName(Name $name, array $oldToNewClasses) : ?Name
    {
        if ($name->getAttribute(AttributeKey::IS_NAMESPACE_NAME) === \true) {
            return null;
        }
        $stringName = $this->nodeNameResolver->getName($name);
        $newName = $oldToNewClasses[$stringName] ?? null;
        if ($newName === null) {
            return null;
        }
        if (!$this->isClassToInterfaceValidChange($name, $newName)) {
            return null;
        }
        // no need to preslash "use \SomeNamespace" of imported namespace
        if ($name->getAttribute(AttributeKey::IS_USEUSE_NAME) === \true) {
            // no need to rename imports, they will be handled by autoimport and coding standard
            // also they might cause some rename
            return null;
        }
        if ($this->shouldSkip($newName, $name)) {
            return null;
        }
        return new FullyQualified($newName);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorNamespace(Namespace_ $namespace, array $oldToNewClasses) : ?Node
    {
        $name = $this->nodeNameResolver->getName($namespace);
        if ($name === null) {
            return null;
        }
        $classLike = $this->getClassOfNamespaceToRefactor($namespace, $oldToNewClasses);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        $currentName = (string) $this->nodeNameResolver->getName($classLike);
        $newClassFullyQualified = $oldToNewClasses[$currentName];
        if ($this->reflectionProvider->hasClass($newClassFullyQualified)) {
            return null;
        }
        $newNamespace = $this->classNaming->getNamespace($newClassFullyQualified);
        // Renaming to class without namespace (example MyNamespace\DateTime -> DateTimeImmutable)
        if (!\is_string($newNamespace)) {
            $classLike->name = new Identifier($newClassFullyQualified);
            return $classLike;
        }
        $namespace->name = new Name($newNamespace);
        return $namespace;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorClassLike(ClassLike $classLike, array $oldToNewClasses, ?Scope $scope) : ?Node
    {
        // rename interfaces
        $this->renameClassImplements($classLike, $oldToNewClasses, $scope);
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $newName = $oldToNewClasses[$className] ?? null;
        if ($newName === null) {
            return null;
        }
        // prevents re-iterating same class in endless loop
        if (\in_array($className, $this->alreadyProcessedClasses, \true)) {
            return null;
        }
        $this->alreadyProcessedClasses[] = $className;
        $newName = $oldToNewClasses[$className];
        $newClassNamePart = $this->nodeNameResolver->getShortName($newName);
        $newNamespacePart = $this->classNaming->getNamespace($newName);
        if ($this->isClassAboutToBeDuplicated($newName)) {
            return null;
        }
        $classLike->name = new Identifier($newClassNamePart);
        $classNamingGetNamespace = $this->classNaming->getNamespace($className);
        // Old class did not have any namespace, we need to wrap class with Namespace_ node
        if ($newNamespacePart !== null && $classNamingGetNamespace === null) {
            $this->changeNameToFullyQualifiedName($classLike);
            $name = new Name($newNamespacePart);
            return new Namespace_($name, [$classLike]);
        }
        return $classLike;
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
    private function isClassToInterfaceValidChange(Name $name, string $newClassName) : bool
    {
        if (!$this->reflectionProvider->hasClass($newClassName)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($newClassName);
        // ensure new is not with interface
        if ($name->getAttribute(AttributeKey::IS_NEW_INSTANCE_NAME) !== \true) {
            return $this->isValidClassNameChange($name, $classReflection);
        }
        if (!$classReflection->isInterface()) {
            return $this->isValidClassNameChange($name, $classReflection);
        }
        return \false;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function getClassOfNamespaceToRefactor(Namespace_ $namespace, array $oldToNewClasses) : ?ClassLike
    {
        $foundClass = $this->betterNodeFinder->findFirst($namespace, function (Node $node) use($oldToNewClasses) : bool {
            if (!$node instanceof ClassLike) {
                return \false;
            }
            $classLikeName = $this->nodeNameResolver->getName($node);
            return isset($oldToNewClasses[$classLikeName]);
        });
        return $foundClass instanceof ClassLike ? $foundClass : null;
    }
    /**
     * @param string[] $oldToNewClasses
     */
    private function renameClassImplements(ClassLike $classLike, array $oldToNewClasses, ?Scope $scope) : void
    {
        if (!$classLike instanceof Class_) {
            return;
        }
        $classLike->implements = \array_unique($classLike->implements);
        foreach ($classLike->implements as $key => $implementName) {
            $virtualNode = (bool) $implementName->getAttribute(AttributeKey::VIRTUAL_NODE);
            if (!$virtualNode) {
                continue;
            }
            $namespaceName = $scope instanceof Scope ? $scope->getNamespace() : null;
            $fullyQualifiedName = $namespaceName . '\\' . $implementName->toString();
            $newName = $oldToNewClasses[$fullyQualifiedName] ?? null;
            if ($newName === null) {
                continue;
            }
            $classLike->implements[$key] = new FullyQualified($newName);
        }
    }
    private function isClassAboutToBeDuplicated(string $newName) : bool
    {
        return $this->reflectionProvider->hasClass($newName);
    }
    private function changeNameToFullyQualifiedName(ClassLike $classLike) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike, static function (Node $node) {
            if (!$node instanceof FullyQualified) {
                return null;
            }
            // invoke override
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        });
    }
    private function isValidClassNameChange(Name $name, ClassReflection $classReflection) : bool
    {
        if ($name->getAttribute(AttributeKey::IS_CLASS_EXTENDS) === \true) {
            // is class to interface?
            if ($classReflection->isInterface()) {
                return \false;
            }
            if ($classReflection->isFinalByKeyword()) {
                return \false;
            }
        }
        if ($name->getAttribute(AttributeKey::IS_CLASS_IMPLEMENT) === \true) {
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
