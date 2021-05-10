<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Removing\Rector\Class_\RemoveTraitRector\RemoveTraitRectorTest
 */
final class RemoveTraitRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TRAITS_TO_REMOVE = 'traits_to_remove';
    /**
     * @var bool
     */
    private $classHasChanged = \false;
    /**
     * @var string[]
     */
    private $traitsToRemove = [];
    /**
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove specific traits from code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::TRAITS_TO_REMOVE => ['TraitNameToRemove']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Trait_::class];
    }
    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $usedTraits = $this->classManipulator->getUsedTraits($node);
        if ($usedTraits === []) {
            return null;
        }
        $this->classHasChanged = \false;
        $this->removeTraits($usedTraits);
        // invoke re-print
        if ($this->classHasChanged) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, string[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->traitsToRemove = $configuration[self::TRAITS_TO_REMOVE] ?? [];
    }
    /**
     * @param Name[] $usedTraits
     */
    private function removeTraits(array $usedTraits) : void
    {
        foreach ($usedTraits as $usedTrait) {
            foreach ($this->traitsToRemove as $traitToRemove) {
                if (!$this->isName($usedTrait, $traitToRemove)) {
                    continue;
                }
                $this->removeNode($usedTrait);
                $this->classHasChanged = \true;
                continue 2;
            }
        }
    }
}
