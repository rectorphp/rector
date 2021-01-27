<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\RenameClassConstFetchRectorTest
 */
final class RenameClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONSTANT_RENAME = 'constant_rename';

    /**
     * @var RenameClassConstFetchInterface[]
     */
    private $renameClassConstFetches = [];

    public function getRuleDefinition(): RuleDefinition
    {
        $configuration = [
            self::CLASS_CONSTANT_RENAME => [
                new RenameClassConstFetch('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'),
                new RenameClassAndConstFetch('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass', 'NEW_CONSTANT'),
            ],
        ];

        return new RuleDefinition(
            'Replaces defined class constants in their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
CODE_SAMPLE
                    ,
                    $configuration
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->renameClassConstFetches as $classConstantRename) {
            if (! $this->isObjectType($node, $classConstantRename->getOldClass())) {
                continue;
            }

            if (! $this->isName($node->name, $classConstantRename->getOldConstant())) {
                continue;
            }

            if ($classConstantRename instanceof RenameClassAndConstFetch) {
                return $this->createClassAndConstFetch($classConstantRename);
            }

            $node->name = new Identifier($classConstantRename->getNewConstant());

            return $node;
        }

        return $node;
    }

    /**
     * @param array<string, RenameClassConstFetchInterface[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $renameClassConstFetches = $configuration[self::CLASS_CONSTANT_RENAME] ?? [];
        Assert::allIsInstanceOf($renameClassConstFetches, RenameClassConstFetchInterface::class);

        $this->renameClassConstFetches = $renameClassConstFetches;
    }

    private function createClassAndConstFetch(RenameClassAndConstFetch $renameClassAndConstFetch): ClassConstFetch
    {
        return new ClassConstFetch(
            new FullyQualified($renameClassAndConstFetch->getNewClass()),
            new Identifier($renameClassAndConstFetch->getNewConstant())
        );
    }
}
