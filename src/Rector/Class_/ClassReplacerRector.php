<?php declare(strict_types=1);

namespace Rector\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ClassReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->oldToNewClasses = $oldToNewClasses;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use SomeOldClass;

function (SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass; 
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use SomeNewClass;

function (SomeNewClass $someOldClass): SomeNewClass
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
        return [Name::class, ClassMethod::class];
    }

    /**
     * @param Name|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Name) {
            $name = $this->getName($node);
            if (! $name) {
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
            $this->docBlockAnalyzer->changeType($node, $oldType, $newType);
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
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
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
        $useNodes = $useUse->getAttribute(Attribute::USE_NODES);
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

        if (in_array($node, $classNode->implements, true) && class_exists($newName)) {
            return false;
        }

        return true;
    }
}
