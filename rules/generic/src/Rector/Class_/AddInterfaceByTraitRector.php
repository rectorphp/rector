<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
final class AddInterfaceByTraitRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const INTERFACE_BY_TRAIT = '$interfaceByTrait';

    /**
     * @var string[]
     */
    private $interfaceByTrait = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add interface by used trait', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    use SomeTrait;
}
PHP
,
                <<<'PHP'
class SomeClass implements SomeInterface
{
    use SomeTrait;
}
PHP
            , [
                '$interfaceByTrait' => [
                    'SomeTrait' => 'SomeInterface',
                ],
            ]),
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
        if ($this->isAnonymousClass($node)) {
            return null;
        }

        $usedTraitNames = $this->classManipulator->getUsedTraits($node);
        if ($usedTraitNames === []) {
            return null;
        }

        $implementedInterfaceNames = $this->classManipulator->getImplementedInterfaceNames($node);

        foreach (array_keys($usedTraitNames) as $traitName) {
            if (! isset($this->interfaceByTrait[$traitName])) {
                continue;
            }

            $interfaceNameToAdd = $this->interfaceByTrait[$traitName];

            if (in_array($interfaceNameToAdd, $implementedInterfaceNames, true)) {
                continue;
            }

            $node->implements[] = new FullyQualified($interfaceNameToAdd);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->interfaceByTrait = $configuration[self::INTERFACE_BY_TRAIT] ?? [];
    }
}
