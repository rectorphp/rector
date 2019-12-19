<?php

declare(strict_types=1);

namespace Rector\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Class_\AddInterfaceByTraitRector\AddInterfaceByTraitRectorTest
 */
final class AddInterfaceByTraitRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $interfaceByTrait = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @param mixed[] $interfaceByTrait
     */
    public function __construct(ClassManipulator $classManipulator, array $interfaceByTrait = [])
    {
        $this->interfaceByTrait = $interfaceByTrait;
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
                    'SomeTrait' => 'SomeInterface'
                ]
            ])
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
            foreach ($this->interfaceByTrait as $seekedTraitName => $interfaceName) {
                if ($traitName !== $seekedTraitName) {
                    continue;
                }

                if (in_array($interfaceName, $implementedInterfaceNames, true)) {
                    continue;
                }

                $node->implements[] = new FullyQualified($interfaceName);
            }
        }

        return $node;
    }
}
