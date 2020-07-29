<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Doctrine\NodeFactory\EntityIdNodeFactory;

/**
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\AddEntityIdByConditionRectorTest
 */
final class AddEntityIdByConditionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const DETECTED_TRAITS = '$detectedTraits';

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
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(
        ClassManipulator $classManipulator,
        EntityIdNodeFactory $entityIdNodeFactory,
        ClassInsertManipulator $classInsertManipulator
    ) {
        $this->classManipulator = $classManipulator;
        $this->entityIdNodeFactory = $entityIdNodeFactory;
        $this->classInsertManipulator = $classInsertManipulator;
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
        $this->classInsertManipulator->addAsFirstMethod($node, $idProperty);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->detectedTraits = $configuration[self::DETECTED_TRAITS] ?? [];
    }

    private function shouldSkip(Class_ $class): bool
    {
        if ($this->isAnonymousClass($class)) {
            return true;
        }

        if (! $this->isTraitMatch($class)) {
            return true;
        }

        return (bool) $class->getProperty('id');
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
}
