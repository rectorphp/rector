<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202312\Webmozart\Assert\Assert;
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
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof TraitUse) {
                continue;
            }
            foreach ($stmt->traits as $traitKey => $trait) {
                if (!$this->isNames($trait, $this->traitsToRemove)) {
                    continue;
                }
                unset($stmt->traits[$traitKey]);
                $hasChanged = \true;
            }
            // remove empty trait uses
            if ($stmt->traits === []) {
                unset($node->stmts[$key]);
            }
        }
        if ($hasChanged) {
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
