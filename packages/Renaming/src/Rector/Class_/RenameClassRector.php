<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDoc\PhpDocClassRenamer;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Renaming\Exception\InvalidPhpCodeException;
use ReflectionClass;

/**
 * @see \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @var string[]
     */
    private $alreadyProcessedClasses = [];

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var PhpDocClassRenamer
     */
    private $phpDocClassRenamer;

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(
        ClassNaming $classNaming,
        PhpDocClassRenamer $phpDocClassRenamer,
        array $oldToNewClasses = []
    ) {
        $this->classNaming = $classNaming;
        $this->oldToNewClasses = $oldToNewClasses;
        $this->phpDocClassRenamer = $phpDocClassRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
PHP
                ,
                <<<'PHP'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
PHP
                ,
                [
                    '$oldToNewClasses' => [
                        'App\SomeOldClass' => 'App\SomeNewClass',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [
            Name::class,
            Property::class,
            FunctionLike::class,
            Expression::class,
            ClassLike::class,
            Namespace_::class,
        ];
    }

    /**
     * @param Name|FunctionLike|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->refactorPhpDoc($node);

        if ($node instanceof Name) {
            return $this->refactorName($node);
        }

        if ($node instanceof Namespace_) {
            return $this->refactorNamespaceNode($node);
        }

        if ($node instanceof ClassLike) {
            return $this->refactorClassLikeNode($node);
        }

        return null;
    }

    /**
     * Replace types in @var/@param/@return/@throws,
     * Doctrine @ORM entity targetClass, Serialize, Assert etc.
     */
    private function refactorPhpDoc(Node $node): void
    {
        $nodePhpDocInfo = $this->getPhpDocInfo($node);
        if ($nodePhpDocInfo === null) {
            return;
        }

        if (! $this->docBlockManipulator->hasNodeTypeTags($node)) {
            return;
        }

        foreach ($this->oldToNewClasses as $oldClass => $newClass) {
            $oldClassType = new ObjectType($oldClass);
            $newClassType = new FullyQualifiedObjectType($newClass);

            $this->docBlockManipulator->changeType($node, $oldClassType, $newClassType);
        }

        $this->phpDocClassRenamer->changeTypeInAnnotationTypes($node, $this->oldToNewClasses);
    }

    private function refactorName(Name $name): ?Name
    {
        $stringName = $this->getName($name);
        if ($stringName === null) {
            return null;
        }

        $newName = $this->oldToNewClasses[$stringName] ?? null;
        if (! $newName) {
            return null;
        }

        if (! $this->isClassToInterfaceValidChange($name, $newName)) {
            return null;
        }

        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        // no need to preslash "use \SomeNamespace" of imported namespace
        if ($parentNode instanceof UseUse && ($parentNode->type === Use_::TYPE_NORMAL || $parentNode->type === Use_::TYPE_UNKNOWN)) {
            return new Name($newName);
        }

        return new FullyQualified($newName);
    }

    private function refactorNamespaceNode(Namespace_ $namespace): ?Node
    {
        $name = $this->getName($namespace);
        if ($name === null) {
            return null;
        }

        $classNode = $this->getClassOfNamespaceToRefactor($namespace);
        if ($classNode === null) {
            return null;
        }

        $newClassFqn = $this->oldToNewClasses[$this->getName($classNode)];
        $newNamespace = $this->classNaming->getNamespace($newClassFqn);

        // Renaming to class without namespace (example MyNamespace\DateTime -> DateTimeImmutable)
        if (! $newNamespace) {
            $classNode->name = new Identifier($newClassFqn);

            return $classNode;
        }

        $namespace->name = new Name($newNamespace);

        return $namespace;
    }

    private function refactorClassLikeNode(ClassLike $classLike): ?Node
    {
        $name = $this->getName($classLike);
        if ($name === null) {
            return null;
        }

        $newName = $this->oldToNewClasses[$name] ?? null;
        if (! $newName) {
            return null;
        }

        // prevents re-iterating same class in endless loop
        if (in_array($name, $this->alreadyProcessedClasses, true)) {
            return null;
        }

        /** @var string $name */
        $this->alreadyProcessedClasses[] = $name;

        $newName = $this->oldToNewClasses[$name];
        $newClassNamePart = $this->classNaming->getShortName($newName);
        $newNamespacePart = $this->classNaming->getNamespace($newName);

        $this->ensureClassWillNotBeDuplicate($newName, $name);

        $classLike->name = new Identifier($newClassNamePart);

        // Old class did not have any namespace, we need to wrap class with Namespace_ node
        if ($newNamespacePart && ! $this->classNaming->getNamespace($name)) {
            $this->changeNameToFullyQualifiedName($classLike);

            return new Namespace_(new Name($newNamespacePart), [$classLike]);
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
    private function isClassToInterfaceValidChange(Node $node, string $newName): bool
    {
        // ensure new is not with interface
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof New_ && interface_exists($newName)) {
            return false;
        }

        if ($parentNode instanceof Class_) {
            return $this->isValidClassNameChange($node, $newName, $parentNode);
        }

        // prevent to change to import, that already exists
        if ($parentNode instanceof UseUse) {
            return $this->isValidUseImportChange($newName, $parentNode);
        }

        return true;
    }

    private function getClassOfNamespaceToRefactor(Namespace_ $namespace): ?ClassLike
    {
        $foundClass = $this->betterNodeFinder->findFirst($namespace, function (Node $node): bool {
            if (! $node instanceof ClassLike) {
                return false;
            }

            $classLikeName = $this->getName($node);

            return isset($this->oldToNewClasses[$classLikeName]);
        });

        return $foundClass instanceof ClassLike ? $foundClass : null;
    }

    private function ensureClassWillNotBeDuplicate(string $newName, string $oldName): void
    {
        if (! ClassExistenceStaticHelper::doesClassLikeExist($newName)) {
            return;
        }

        $classReflection = new ReflectionClass($newName);

        throw new InvalidPhpCodeException(sprintf(
            'Renaming class "%s" to "%s" would create a duplicated class/interface/trait (already existing in "%s") and cause PHP code to be invalid.',
            $oldName,
            $newName,
            $classReflection->getFileName()
        ));
    }

    private function changeNameToFullyQualifiedName(ClassLike $classLike): void
    {
        $this->traverseNodesWithCallable($classLike, function (Node $node) {
            if (! $node instanceof FullyQualified) {
                return null;
            }

            // invoke override
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        });
    }

    private function isValidClassNameChange(Node $node, string $newName, Class_ $classNode): bool
    {
        if ($classNode->extends === $node && interface_exists($newName)) {
            return false;
        }
        return ! (in_array($node, $classNode->implements, true) && class_exists($newName));
    }

    private function isValidUseImportChange(string $newName, UseUse $useUse): bool
    {
        /** @var Use_[]|null $useNodes */
        $useNodes = $useUse->getAttribute(AttributeKey::USE_NODES);
        if ($useNodes === null) {
            return true;
        }

        foreach ($useNodes as $useNode) {
            if ($this->isName($useNode, $newName)) {
                // name already exists
                return false;
            }
        }

        return true;
    }
}
