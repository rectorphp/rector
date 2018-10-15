<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Builder\IdentifierRenamer;
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
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @param string[][] $oldToNewConstantsByClass
     */
    public function __construct(array $oldToNewConstantsByClass, IdentifierRenamer $identifierRenamer)
    {
        $this->oldToNewConstantsByClass = $oldToNewConstantsByClass;
        $this->identifierRenamer = $identifierRenamer;
    }

    /**
     * @todo complete list with all possibilities
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                '$value = SomeClass::OLD_CONSTANT;',
                '$value = SomeClass::NEW_CONSTANT;',
                [
                    '$oldToNewConstantsByClass' => [
                        'SomeClass' => [
                            'OLD_CONSTANT' => 'NEW_CONSTANT',
                        ],
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
                if (! $this->isName($node, $oldConstant)) {
                    continue;
                }

                $this->identifierRenamer->renameNode($node, $newConstant);
                return $node;
            }
        }

        return $node;
    }
}
