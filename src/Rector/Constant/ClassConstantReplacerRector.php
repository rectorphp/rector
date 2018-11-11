<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ClassConstantReplacerRector extends AbstractRector
{
    /**
     * class => [
     *      OLD_CONSTANT => NEW_CONSTANT
     * ]
     *
     * @var string[][]
     */
    private $oldToNewConstantsByClass = [];

    /**
     * @param string[][] $oldToNewConstantsByClass
     */
    public function __construct(array $oldToNewConstantsByClass)
    {
        $this->oldToNewConstantsByClass = $oldToNewConstantsByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                '$value = SomeClass::OLD_CONSTANT;',
                '$value = SomeClass::NEW_CONSTANT;',
                [
                    'SomeClass' => [
                        'OLD_CONSTANT' => 'NEW_CONSTANT',
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
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($oldToNewConstants as $oldConstant => $newConstant) {
                if (! $this->isNameInsensitive($node, $oldConstant)) {
                    continue;
                }

                $node->name = new Identifier($newConstant);

                return $node;
            }
        }

        return $node;
    }
}
