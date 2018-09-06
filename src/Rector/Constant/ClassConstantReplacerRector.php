<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\ClassConstAnalyzer;
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
     * @var string[]
     */
    private $oldToNewConstantsByClass = [];

    /**
     * @var ClassConstAnalyzer
     */
    private $classConstAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @param string[] $oldToNewConstantsByClass
     */
    public function __construct(
        array $oldToNewConstantsByClass,
        ClassConstAnalyzer $classConstAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->oldToNewConstantsByClass = $oldToNewConstantsByClass;
        $this->classConstAnalyzer = $classConstAnalyzer;
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
        $activeType = $this->classConstAnalyzer->matchTypes($classConstFetchNode, $this->getTypes());
        if ($activeType === null) {
            return null;
        }

        $configuration = $this->oldToNewConstantsByClass[$activeType];

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        $constantName = $identifierNode->toString();

        $newConstantName = $configuration[$constantName];

        if ($newConstantName === '') {
            return $classConstFetchNode;
        }

        $this->identifierRenamer->renameNode($classConstFetchNode, $newConstantName);

        return $classConstFetchNode;
    }

    /**
     * @return string[]
     */
    private function getTypes(): array
    {
        return array_keys($this->oldToNewConstantsByClass);
    }
}
