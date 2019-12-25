<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\NodeFactory\EntityIdNodeFactory;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\AddEntityIdByConditionRectorTest
 */
final class AddEntityIdByConditionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $detectedTraits = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var EntityIdNodeFactory
     */
    private $entityIdNodeFactory;

    /**
     * @param string[] $detectedTraits
     */
    public function __construct(
        ClassManipulator $classManipulator,
        EntityIdNodeFactory $entityIdNodeFactory,
        array $detectedTraits = []
    ) {
        $this->detectedTraits = $detectedTraits;
        $this->classManipulator = $classManipulator;
        $this->entityIdNodeFactory = $entityIdNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add entity id with annotations when meets condition', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    use SomeTrait;
}
PHP
,
                <<<'PHP'
class SomeClass
{
    use SomeTrait;

    /**
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue(strategy="AUTO")
      */
     private $id;

    public function getId(): int
    {
        return $this->id;
    }
}
PHP
, []),
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $idProperty = $this->entityIdNodeFactory->createIdProperty();
        $this->classManipulator->addAsFirstMethod($node, $idProperty);

        return $node;
    }

    private function isTraitMatch(Class_ $class): bool
    {
        $usedTraits = $this->classManipulator->getUsedTraits($class);

        foreach (array_keys($usedTraits) as $traitName) {
            foreach ($this->detectedTraits as $detectedTrait) {
                if ($traitName === $detectedTrait) {
                    return true;
                }
            }
        }

        return false;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if ($this->isAnonymousClass($class)) {
            return true;
        }

        if (! $this->isTraitMatch($class)) {
            return true;
        }

        if ($this->classManipulator->hasPropertyName($class, 'id')) {
            return true;
        }

        return false;
    }
}
