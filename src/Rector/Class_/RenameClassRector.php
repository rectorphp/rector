<?php declare(strict_types=1);

namespace Rector\Rector\Class_;

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
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpDoc\PhpDocClassRenamer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Class_\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

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
        DocBlockManipulator $docBlockManipulator,
        ClassNaming $classNaming,
        PhpDocClassRenamer $phpDocClassRenamer,
        array $oldToNewClasses = []
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->classNaming = $classNaming;
        $this->oldToNewClasses = $oldToNewClasses;
        $this->phpDocClassRenamer = $phpDocClassRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass; 
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
CODE_SAMPLE
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
        // replace on @var/@param/@return/@throws
        if ($this->docBlockManipulator->hasNodeTypeChangeableTags($node)) {
            foreach ($this->oldToNewClasses as $oldClass => $newClass) {
                $this->docBlockManipulator->changeType($node, $oldClass, $newClass);
            }
        }

        $this->phpDocClassRenamer->changeTypeInAnnotationTypes($node, $this->oldToNewClasses);

        if ($node instanceof Name) {
            return $this->refactorName($node);
        }

        if ($node instanceof Namespace_) {
            $node = $this->refactorNamespaceNode($node);
        }

        if ($node instanceof ClassLike) {
            $node = $this->refactorClassLikeNode($node);
        }

        if ($node === null) {
            return null;
        }

        return $node;
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

    private function isValidClassNameChange(Node $node, string $newName, Class_ $classNode): bool
    {
        if ($classNode->extends === $node && interface_exists($newName)) {
            return false;
        }
        return ! (in_array($node, $classNode->implements, true) && class_exists($newName));
    }

    private function refactorNamespaceNode(Namespace_ $node): ?Node
    {
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }

        $classNode = $this->getClassOfNamespaceToRefactor($node);
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

        $node->name = new Name($newNamespace);

        return $node;
    }

    private function getClassOfNamespaceToRefactor(Namespace_ $namespace): ?ClassLike
    {
        $foundClass = $this->betterNodeFinder->findFirst($namespace, function (Node $node): bool {
            if (! $node instanceof ClassLike) {
                return false;
            }

            return isset($this->oldToNewClasses[$this->getName($node)]);
        });

        return $foundClass instanceof ClassLike ? $foundClass : null;
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

        $this->alreadyProcessedClasses[] = $name;

        $newName = $this->oldToNewClasses[$name];
        $newClassNamePart = $this->classNaming->getShortName($newName);
        $newNamespacePart = $this->classNaming->getNamespace($newName);

        $classLike->name = new Identifier($newClassNamePart);

        // Old class did not have any namespace, we need to wrap class with Namespace_ node
        if ($newNamespacePart && ! $this->classNaming->getNamespace($name)) {
            return new Namespace_(new Name($newNamespacePart), [$classLike]);
        }

        return $classLike;
    }

    private function refactorName(Node $node): ?Name
    {
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }

        $newName = $this->oldToNewClasses[$name] ?? null;
        if (! $newName) {
            return null;
        }

        if (! $this->isClassToInterfaceValidChange($node, $newName)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // no need to preslash "use \SomeNamespace" of imported namespace
        if ($parentNode instanceof UseUse) {
            if ($parentNode->type === Use_::TYPE_NORMAL || $parentNode->type === Use_::TYPE_UNKNOWN) {
                return new Name($newName);
            }
        }

        return new FullyQualified($newName);
    }
}
