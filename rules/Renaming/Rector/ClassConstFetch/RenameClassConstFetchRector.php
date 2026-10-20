<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\RenameClassConstFetchRectorTest
 */
final class RenameClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, RenameClassConstFetchInterface[]>
     */
    private array $renameClassConstFetchesByOldConstant = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace defined class constants in their calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
CODE_SAMPLE
, [new RenameClassConstFetch('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'), new RenameClassAndConstFetch('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass', 'NEW_CONSTANT')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?ClassConstFetch
    {
        $constantName = $this->getName($node->name);
        if ($constantName === null) {
            return null;
        }
        foreach ($this->renameClassConstFetchesByOldConstant[strtolower($constantName)] ?? [] as $renameClassConstFetch) {
            if (!$this->isObjectType($node->class, $renameClassConstFetch->getOldObjectType())) {
                continue;
            }
            if ($renameClassConstFetch instanceof RenameClassAndConstFetch) {
                return $this->createClassAndConstFetch($renameClassConstFetch);
            }
            $node->name = new Identifier($renameClassConstFetch->getNewConstant());
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RenameClassConstFetchInterface::class);
        // bucket by lowercased old constant name, matching the case-insensitive isName() lookup
        $this->renameClassConstFetchesByOldConstant = [];
        foreach ($configuration as $renameClassConstFetch) {
            $this->renameClassConstFetchesByOldConstant[strtolower($renameClassConstFetch->getOldConstant())][] = $renameClassConstFetch;
        }
    }
    private function createClassAndConstFetch(RenameClassAndConstFetch $renameClassAndConstFetch): ClassConstFetch
    {
        return new ClassConstFetch(new FullyQualified($renameClassAndConstFetch->getNewClass()), new Identifier($renameClassAndConstFetch->getNewConstant()));
    }
}
