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
use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Renaming\Helper\RenameClassCallbackHandler;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202301\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CALLBACKS = '#callbacks#';
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\ClassRenamer
     */
    private $classRenamer;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    /**
     * @readonly
     * @var \Rector\Renaming\Helper\RenameClassCallbackHandler
     */
    private $renameClassCallbackHandler;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, ClassRenamer $classRenamer, RectorConfigProvider $rectorConfigProvider, RenameClassCallbackHandler $renameClassCallbackHandler)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->classRenamer = $classRenamer;
        $this->rectorConfigProvider = $rectorConfigProvider;
        $this->renameClassCallbackHandler = $renameClassCallbackHandler;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces defined classes by new ones.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, ['App\\SomeOldClass' => 'App\\SomeNewClass'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Name::class, Property::class, FunctionLike::class, Expression::class, ClassLike::class, Namespace_::class, FileWithoutNamespace::class, Use_::class];
    }
    /**
     * @param FunctionLike|Name|ClassLike|Expression|Namespace_|Property|FileWithoutNamespace|Use_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === [] && !$this->renameClassCallbackHandler->hasOldToNewClassCallbacks()) {
            return null;
        }
        if (!$node instanceof Use_) {
            return $this->classRenamer->renameNode($node, $oldToNewClasses);
        }
        if (!$this->rectorConfigProvider->shouldImportNames()) {
            return null;
        }
        return $this->processCleanUpUse($node, $oldToNewClasses);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $oldToNewClassCallbacks = $configuration[self::CALLBACKS] ?? [];
        Assert::isArray($oldToNewClassCallbacks);
        if ($oldToNewClassCallbacks !== []) {
            Assert::allIsCallable($oldToNewClassCallbacks);
            /** @var array<callable(ClassLike, NodeNameResolver): ?string> $oldToNewClassCallbacks */
            $this->renameClassCallbackHandler->addOldToNewClassCallbacks($oldToNewClassCallbacks);
            unset($configuration[self::CALLBACKS]);
        }
        Assert::allString($configuration);
        Assert::allString(\array_keys($configuration));
        $this->addOldToNewClasses($configuration);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processCleanUpUse(Use_ $use, array $oldToNewClasses) : ?Use_
    {
        foreach ($use->uses as $useUse) {
            if (!$useUse->alias instanceof Identifier && isset($oldToNewClasses[$useUse->name->toString()])) {
                $this->removeNode($use);
                return $use;
            }
        }
        return null;
    }
    /**
     * @param mixed[] $oldToNewClasses
     */
    private function addOldToNewClasses(array $oldToNewClasses) : void
    {
        Assert::allString(\array_keys($oldToNewClasses));
        Assert::allString($oldToNewClasses);
        $this->renamedClassesDataCollector->addOldToNewClasses($oldToNewClasses);
    }
}
