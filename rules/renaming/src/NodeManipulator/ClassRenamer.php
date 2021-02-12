<?php

declare(strict_types=1);

namespace Rector\Renaming\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocClassRenamer;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use ReflectionClass;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassRenamer
{
    /**
     * @var string[]
     */
    private $alreadyProcessedClasses = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var PhpDocClassRenamer
     */
    private $phpDocClassRenamer;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        ClassNaming $classNaming,
        NodeNameResolver $nodeNameResolver,
        PhpDocClassRenamer $phpDocClassRenamer,
        PhpDocInfoFactory $phpDocInfoFactory,
        DocBlockClassRenamer $docBlockClassRenamer
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->phpDocClassRenamer = $phpDocClassRenamer;
        $this->classNaming = $classNaming;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function renameNode(Node $node, array $oldToNewClasses): ?Node
    {
        $this->refactorPhpDoc($node, $oldToNewClasses);

        if ($node instanceof Name) {
            return $this->refactorName($node, $oldToNewClasses);
        }

        if ($node instanceof Namespace_) {
            return $this->refactorNamespace($node, $oldToNewClasses);
        }

        if ($node instanceof ClassLike) {
            return $this->refactorClassLike($node, $oldToNewClasses);
        }

        return null;
    }

    /**
     * Replace types in @var/@param/@return/@throws,
     * Doctrine @ORM entity targetClass, Serialize, Assert etc.
     *
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorPhpDoc(Node $node, array $oldToNewClasses): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (! $phpDocInfo->hasByType(TypeAwareTagValueNodeInterface::class)) {
            return;
        }

        foreach ($oldToNewClasses as $oldClass => $newClass) {
            $oldClassType = new ObjectType($oldClass);
            $newClassType = new FullyQualifiedObjectType($newClass);

            $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldClassType, $newClassType, $node);
        }

        $this->phpDocClassRenamer->changeTypeInAnnotationTypes($phpDocInfo, $oldToNewClasses);
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorName(Name $name, array $oldToNewClasses): ?Name
    {
        $stringName = $this->nodeNameResolver->getName($name);

        $newName = $oldToNewClasses[$stringName] ?? null;
        if (! $newName) {
            return null;
        }

        if (! $this->isClassToInterfaceValidChange($name, $newName)) {
            return null;
        }

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        // no need to preslash "use \SomeNamespace" of imported namespace
        if ($parentNode instanceof UseUse && ($parentNode->type === Use_::TYPE_NORMAL || $parentNode->type === Use_::TYPE_UNKNOWN)) {

            // no need to rename imports, they will be handled by autoimport and coding standard
            // also they might cause some rename
            return null;
        }
        $name = new FullyQualified($newName);

        $name->setAttribute(AttributeKey::PARENT_NODE, $parentNode);

        return $name;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorNamespace(Namespace_ $namespace, array $oldToNewClasses): ?Node
    {
        $name = $this->nodeNameResolver->getName($namespace);
        if ($name === null) {
            return null;
        }

        $classLike = $this->getClassOfNamespaceToRefactor($namespace, $oldToNewClasses);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        $currentName = $this->nodeNameResolver->getName($classLike);
        $newClassFqn = $oldToNewClasses[$currentName];

        if (ClassExistenceStaticHelper::doesClassLikeExist($newClassFqn)) {
            return null;
        }

        $newNamespace = $this->classNaming->getNamespace($newClassFqn);
        // Renaming to class without namespace (example MyNamespace\DateTime -> DateTimeImmutable)
        if (! $newNamespace) {
            $classLike->name = new Identifier($newClassFqn);

            return $classLike;
        }

        $namespace->name = new Name($newNamespace);

        return $namespace;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function refactorClassLike(ClassLike $classLike, array $oldToNewClasses): ?Node
    {
        // rename interfaces
        $this->renameClassImplements($classLike, $oldToNewClasses);

        $name = $this->nodeNameResolver->getName($classLike);
        if ($name === null) {
            return null;
        }

        $newName = $oldToNewClasses[$name] ?? null;
        if (! $newName) {
            return null;
        }

        // prevents re-iterating same class in endless loop
        if (in_array($name, $this->alreadyProcessedClasses, true)) {
            return null;
        }

        /** @var string $name */
        $this->alreadyProcessedClasses[] = $name;

        $newName = $oldToNewClasses[$name];
        $newClassNamePart = $this->nodeNameResolver->getShortName($newName);
        $newNamespacePart = $this->classNaming->getNamespace($newName);

        if ($this->isClassAboutToBeDuplicated($newName)) {
            return null;
        }

        $classLike->name = new Identifier($newClassNamePart);
        $classNamingGetNamespace = $this->classNaming->getNamespace($name);

        // Old class did not have any namespace, we need to wrap class with Namespace_ node
        if ($newNamespacePart && ! $classNamingGetNamespace) {
            $this->changeNameToFullyQualifiedName($classLike);

            $nameNode = new Name($newNamespacePart);
            $namespace = new Namespace_($nameNode, [$classLike]);
            $nameNode->setAttribute(AttributeKey::PARENT_NODE, $namespace);

            return $namespace;
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
    private function isClassToInterfaceValidChange(Name $name, string $newName): bool
    {
        // ensure new is not with interface
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof New_ && interface_exists($newName)) {
            return false;
        }

        if ($parentNode instanceof Class_) {
            return $this->isValidClassNameChange($name, $newName, $parentNode);
        }

        // prevent to change to import, that already exists
        if ($parentNode instanceof UseUse) {
            return $this->isValidUseImportChange($newName, $parentNode);
        }

        return true;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function getClassOfNamespaceToRefactor(Namespace_ $namespace, array $oldToNewClasses): ?ClassLike
    {
        $foundClass = $this->betterNodeFinder->findFirst($namespace, function (Node $node) use (
            $oldToNewClasses
        ): bool {
            if (! $node instanceof ClassLike) {
                return false;
            }

            $classLikeName = $this->nodeNameResolver->getName($node);

            return isset($oldToNewClasses[$classLikeName]);
        });

        return $foundClass instanceof ClassLike ? $foundClass : null;
    }

    /**
     * @param string[] $oldToNewClasses
     */
    private function renameClassImplements(ClassLike $classLike, array $oldToNewClasses): void
    {
        if (! $classLike instanceof Class_) {
            return;
        }

        $classLike->implements = array_unique($classLike->implements);
        foreach ($classLike->implements as $key => $implementName) {
            if (! $implementName instanceof Name) {
                continue;
            }
            $virtualNode = $implementName->getAttribute(AttributeKey::VIRTUAL_NODE);

            if (! $virtualNode) {
                continue;
            }

            $namespaceName = $classLike->getAttribute(AttributeKey::NAMESPACE_NAME);
            $fullyQualifiedName = $namespaceName . '\\' . $implementName->toString();
            $newName = $oldToNewClasses[$fullyQualifiedName] ?? null;
            if ($newName === null) {
                continue;
            }

            $classLike->implements[$key] = new FullyQualified($newName);
        }
    }

    private function isClassAboutToBeDuplicated(string $newName): bool
    {
        return ClassExistenceStaticHelper::doesClassLikeExist($newName);
    }

    private function changeNameToFullyQualifiedName(ClassLike $classLike): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classLike, function (Node $node) {
            if (! $node instanceof FullyQualified) {
                return null;
            }

            // invoke override
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        });
    }

    private function isValidClassNameChange(Name $name, string $newName, Class_ $class): bool
    {
        // is class to interface?
        if ($class->extends === $name && interface_exists($newName)) {
            return false;
        }

        // is interface to class?
        if (in_array($name, $class->implements, true) && class_exists($newName)) {
            return false;
        }

        if ($class->extends === $name && class_exists($newName)) {
            // is final class?
            $reflectionClass = new ReflectionClass($newName);
            if ($reflectionClass->isFinal()) {
                return false;
            }
        }

        return true;
    }

    private function isValidUseImportChange(string $newName, UseUse $useUse): bool
    {
        /** @var Use_[]|null $useNodes */
        $useNodes = $useUse->getAttribute(AttributeKey::USE_NODES);
        if ($useNodes === null) {
            return true;
        }

        foreach ($useNodes as $useNode) {
            if ($this->nodeNameResolver->isName($useNode, $newName)) {
                // name already exists
                return false;
            }
        }

        return true;
    }
}
