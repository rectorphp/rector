<?php declare(strict_types=1);

namespace Rector\Rector\Interface_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveInterfacesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $interfacesToRemove = [];

    /**
     * @param string[] $interfacesToRemove
     */
    public function __construct(array $interfacesToRemove)
    {
        $this->interfacesToRemove = $interfacesToRemove;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes interfaces usage from class.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
                ,
                ['SomeInterface']
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
        if (! $node->implements) {
            return null;
        }

        foreach ($node->implements as $key => $implement) {
            if ($this->isNames($implement, $this->interfacesToRemove)) {
                unset($node->implements[$key]);
            }
        }

        $node->implements = array_values($node->implements);

        return $node;
    }
}
