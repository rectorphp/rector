<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Can handle cases like:
 * - https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 * - https://github.com/silverstripe/silverstripe-upgrader/issues/71#issue-320145944
 *
 * @see \Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\ParentClassToTraitsRectorTest
 */
final class ParentClassToTraitsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARENT_CLASS_TO_TRAITS = '$parentClassToTraits';

    /**
     * @var string[][] { parent class => [ traits ] }
     */
    private $parentClassToTraits = [];

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces parent class to specific traits', [
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
                [
                    self::PARENT_CLASS_TO_TRAITS => [
                        'Nette\Object' => ['Nette\SmartObject'],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
        if ($node->extends === null || $node->isAnonymous()) {
            return null;
        }

        $nodeParentClassName = $this->getName($node->extends);
        if (! isset($this->parentClassToTraits[$nodeParentClassName])) {
            return null;
        }

        $traitNames = $this->parentClassToTraits[$nodeParentClassName];

        // keep the Trait order the way it is in config
        $traitNames = array_reverse($traitNames);

        foreach ($traitNames as $traitName) {
            $this->classInsertManipulator->addAsFirstTrait($node, $traitName);
        }

        $this->removeParentClass($node);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->parentClassToTraits = $configuration[self::PARENT_CLASS_TO_TRAITS] ?? [];
    }

    private function removeParentClass(Class_ $class): void
    {
        $class->extends = null;
    }
}
