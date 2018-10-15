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
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor(Node $classConstFetchNode): ?Node
    {
        $currentConstantName = (string) $classConstFetchNode->name;

        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            if (! $this->isType($classConstFetchNode, $type)) {
                continue;
            }

            foreach ($oldToNewConstants as $oldConstant => $newConstant) {
                if ($currentConstantName !== $oldConstant) {
                    continue;
                }

                $this->identifierRenamer->renameNode($classConstFetchNode, $newConstant);
                return $classConstFetchNode;
            }
        }

        return $classConstFetchNode;
    }
}
