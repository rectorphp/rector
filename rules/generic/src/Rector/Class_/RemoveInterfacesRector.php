<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\RemoveInterfacesRectorTest
 */
final class RemoveInterfacesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const INTERFACES_TO_REMOVE = '$interfacesToRemove';

    /**
     * @var string[]
     */
    private $interfacesToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes interfaces usage from class.', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass implements SomeInterface
{
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
}
PHP
                ,
                [
                    self::INTERFACES_TO_REMOVE => ['SomeInterface'],
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
        if ($node->implements === []) {
            return null;
        }

        foreach ($node->implements as $key => $implement) {
            if ($this->isNames($implement, $this->interfacesToRemove)) {
                unset($node->implements[$key]);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->interfacesToRemove = $configuration[self::INTERFACES_TO_REMOVE] ?? [];
    }
}
