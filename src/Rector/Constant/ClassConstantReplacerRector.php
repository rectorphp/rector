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
     * @var string|null
     */
    private $activeType;

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

    public function getNodeType(): string
    {
        return ClassConstFetch::class;
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor(Node $classConstFetchNode): ?Node
    {
        $this->activeType = null;
        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            $matchedType = $this->classConstAnalyzer->matchTypes($classConstFetchNode, $this->getTypes());
            if ($matchedType) {
                $this->activeType = $matchedType;
            }
        }
        return null;
        $configuration = $this->oldToNewConstantsByClass[$this->activeType];

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
