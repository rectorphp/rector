<?php

declare(strict_types=1);

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
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_CLASSES = 'old_to_new_classes';

    public function __construct(
        private RenamedClassesDataCollector $renamedClassesDataCollector,
        private ClassRenamer $classRenamer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces defined classes by new ones.', [
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
                    self::OLD_TO_NEW_CLASSES => [
                        'App\SomeOldClass' => 'App\SomeNewClass',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
            FileWithoutNamespace::class,
            Use_::class,
        ];
    }

    /**
     * @param FunctionLike|Name|ClassLike|Expression|Namespace_|Property|FileWithoutNamespace|Use_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();

        if (! $node instanceof Use_) {
            return $this->classRenamer->renameNode($node, $oldToNewClasses);
        }

        if (! $this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return null;
        }

        return $this->processCleanUpUse($node, $oldToNewClasses);
    }

    /**
     * @param array<string, mixed[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $oldToNewClasses = $configuration[self::OLD_TO_NEW_CLASSES] ?? $configuration;

        Assert::isArray($oldToNewClasses);
        Assert::allString($oldToNewClasses);

        $this->addOldToNewClasses($oldToNewClasses);
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function processCleanUpUse(Use_ $use, array $oldToNewClasses): ?Use_
    {
        foreach ($use->uses as $useUse) {
            if ($useUse->name instanceof Name && ! $useUse->alias instanceof Identifier && isset($oldToNewClasses[$useUse->name->toString()])) {
                $this->removeNode($use);
                return $use;
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    private function addOldToNewClasses(array $oldToNewClasses): void
    {
        Assert::allString(array_keys($oldToNewClasses));
        Assert::allString($oldToNewClasses);

        $this->renamedClassesDataCollector->addOldToNewClasses($oldToNewClasses);
    }
}
