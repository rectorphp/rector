<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\Class_\RemoveTraitUseRector\RemoveTraitUseRectorTest
 */
final class RemoveTraitUseRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $traitsToRemove = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove specific traits from code', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    use SomeTrait;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
, ['TraitNameToRemove'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Trait_::class];
    }
    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classHasChanged = \false;
        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                if (!$this->isNames($trait, $this->traitsToRemove)) {
                    continue;
                }
                $this->removeNode($traitUse);
                $classHasChanged = \true;
            }
        }
        if ($classHasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        $this->traitsToRemove = $configuration;
    }
}
