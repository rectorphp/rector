<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Can handle cases like:
 * - https://doc.nette.org/en/2.4/migration-2-4#toc-nette-smartobject
 * - https://github.com/silverstripe/silverstripe-upgrader/issues/71#issue-320145944
 * @see \Rector\Core\Tests\Rector\Class_\ParentClassToTraitsRector\ParentClassToTraitsRectorTest
 */
final class ParentClassToTraitsRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $parentClassToTraits = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @param string[][] $parentClassToTraits { parent class => [ traits ] }
     */
    public function __construct(ClassManipulator $classManipulator, array $parentClassToTraits = [])
    {
        $this->classManipulator = $classManipulator;
        $this->parentClassToTraits = $parentClassToTraits;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces parent class to specific traits', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass extends Nette\Object
{
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    use Nette\SmartObject;
}
PHP
                ,
                [
                    'Nette\Object' => ['Nette\SmartObject'],
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
            $this->classManipulator->addAsFirstTrait($node, $traitName);
        }

        $this->removeParentClass($node);

        return $node;
    }

    private function removeParentClass(Class_ $classNode): void
    {
        $classNode->extends = null;
    }
}
