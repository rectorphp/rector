<?php declare(strict_types=1);

namespace Rector\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @param string[] $oldToNewClasses
     */
    public function __construct(DocBlockManipulator $docBlockManipulator, array $oldToNewClasses = [])
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->oldToNewClasses = $oldToNewClasses;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
                    'SomeOldClass' => 'SomeNewClass',
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Name::class, Property::class, FunctionLike::class, Expression::class];
    }

    /**
     * @param Name|FunctionLike|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        // replace on @var/@param/@return/@throws
        foreach ($this->oldToNewClasses as $oldClass => $newClass) {
            $this->docBlockManipulator->changeType($node, $oldClass, $newClass);
        }

        if ($node instanceof Name) {
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

            return new FullyQualified($newName);
        }

        foreach ($this->oldToNewClasses as $oldType => $newType) {
            $this->docBlockManipulator->changeType($node, $oldType, $newType);
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
}
