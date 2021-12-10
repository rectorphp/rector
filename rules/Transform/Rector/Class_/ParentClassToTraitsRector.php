<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\ParentClassToTraits;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * Can handle cases like:
 * - https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 * - https://github.com/silverstripe/silverstripe-upgrader/issues/71#issue-320145944
 *
 * @see \Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\ParentClassToTraitsRectorTest
 */
final class ParentClassToTraitsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    final public const PARENT_CLASS_TO_TRAITS = 'parent_class_to_traits';

    /**
     * @var ParentClassToTraits[]
     */
    private array $parentClassToTraits = [];

    public function __construct(
        private readonly ClassInsertManipulator $classInsertManipulator,
        private readonly ClassAnalyzer $classAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces parent class to specific traits', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends Nette\Object
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    use Nette\SmartObject;
}
CODE_SAMPLE
                ,
                [new ParentClassToTraits('Nette\Object', ['Nette\SmartObject'])]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null) {
            return null;
        }

        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }

        foreach ($this->parentClassToTraits as $parentClassToTrait) {
            if (! $this->isObjectType($node, $parentClassToTrait->getParentObjectType())) {
                continue;
            }

            foreach ($parentClassToTrait->getTraitNames() as $traitName) {
                $this->classInsertManipulator->addAsFirstTrait($node, $traitName);
            }

            $this->removeParentClass($node);

            return $node;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $parentClassToTraits = $configuration[self::PARENT_CLASS_TO_TRAITS] ?? $configuration;
        Assert::allIsAOf($parentClassToTraits, ParentClassToTraits::class);
        $this->parentClassToTraits = $parentClassToTraits;
    }

    private function removeParentClass(Class_ $class): void
    {
        $class->extends = null;
    }
}
