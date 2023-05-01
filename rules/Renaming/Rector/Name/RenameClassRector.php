<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Renaming\Helper\RenameClassCallbackHandler;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
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
     * @var \Rector\Renaming\Helper\RenameClassCallbackHandler
     */
    private $renameClassCallbackHandler;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, ClassRenamer $classRenamer, RenameClassCallbackHandler $renameClassCallbackHandler)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->classRenamer = $classRenamer;
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
        return [Name::class, Property::class, FunctionLike::class, Expression::class, ClassLike::class, Namespace_::class];
    }
    /**
     * @param FunctionLike|Name|ClassLike|Expression|Namespace_|Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses !== []) {
            return $this->classRenamer->renameNode($node, $oldToNewClasses);
        }
        if ($this->renameClassCallbackHandler->hasOldToNewClassCallbacks()) {
            return $this->classRenamer->renameNode($node, $oldToNewClasses);
        }
        return null;
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
     * @param mixed[] $oldToNewClasses
     */
    private function addOldToNewClasses(array $oldToNewClasses) : void
    {
        Assert::allString(\array_keys($oldToNewClasses));
        Assert::allString($oldToNewClasses);
        $this->renamedClassesDataCollector->addOldToNewClasses($oldToNewClasses);
    }
}
