<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_CLASSES = 'old_to_new_classes';
    /**
     * @api
     * @var string
     */
    public const CLASS_MAP_FILES = 'class_map_files';
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @var \Rector\Renaming\NodeManipulator\ClassRenamer
     */
    private $classRenamer;
    public function __construct(\Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector, \Rector\Renaming\NodeManipulator\ClassRenamer $classRenamer)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->classRenamer = $classRenamer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces defined classes by new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
CODE_SAMPLE
, [self::OLD_TO_NEW_CLASSES => ['App\\SomeOldClass' => 'App\\SomeNewClass']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Name::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\FunctionLike::class, \PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\ClassLike::class, \PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class, \PhpParser\Node\Stmt\Use_::class];
    }
    /**
     * @param FunctionLike|Name|ClassLike|Expression|Namespace_|Property|FileWithoutNamespace|Use_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if (!$node instanceof \PhpParser\Node\Stmt\Use_) {
            return $this->classRenamer->renameNode($node, $oldToNewClasses);
        }
        if (!$this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES)) {
            return null;
        }
        return $this->processCleanUpUse($node, $oldToNewClasses);
    }
    /**
     * @param array<string, array<string, string>> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->addOldToNewClasses($configuration[self::OLD_TO_NEW_CLASSES] ?? []);
        $classMapFiles = $configuration[self::CLASS_MAP_FILES] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allString($classMapFiles);
        foreach ($classMapFiles as $classMapFile) {
            \RectorPrefix20211020\Webmozart\Assert\Assert::fileExists($classMapFile);
            $oldToNewClasses = (require_once $classMapFile);
            $this->addOldToNewClasses($oldToNewClasses);
        }
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processCleanUpUse(\PhpParser\Node\Stmt\Use_ $use, array $oldToNewClasses) : ?\PhpParser\Node\Stmt\Use_
    {
        foreach ($use->uses as $useUse) {
            if ($useUse->name instanceof \PhpParser\Node\Name && !$useUse->alias instanceof \PhpParser\Node\Identifier && isset($oldToNewClasses[$useUse->name->toString()])) {
                $this->removeNode($use);
                return $use;
            }
        }
        return null;
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function addOldToNewClasses(array $oldToNewClasses) : void
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::allString(\array_keys($oldToNewClasses));
        \RectorPrefix20211020\Webmozart\Assert\Assert::allString($oldToNewClasses);
        $this->renamedClassesDataCollector->addOldToNewClasses($oldToNewClasses);
    }
}
