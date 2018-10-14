<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RenameClassConstantsUseToStringsRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string[][]
     */
    private $oldConstantsToNewValuesByType = [];

    /**
     * @param string[][] $oldConstantsToNewValuesByType
     */
    public function __construct(NodeFactory $nodeFactory, array $oldConstantsToNewValuesByType)
    {
        $this->nodeFactory = $nodeFactory;
        $this->oldConstantsToNewValuesByType = $oldConstantsToNewValuesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces constant by value', [
            new ConfiguredCodeSample(
                '$value === Nette\Configurator::DEVELOPMENT',
                '$value === "development"',
                [
                    'Nette\Configurator' => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
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
        $className = $this->getClassNameFromClassConstFetch($classConstFetchNode);

        foreach ($this->oldConstantsToNewValuesByType as $type => $oldConstantsToNewValues) {
            if ($className !== $type) {
                continue;
            }

            foreach ($oldConstantsToNewValues as $oldConstant => $newValue) {
                if ((string) $classConstFetchNode->name === $oldConstant) {
                    return $this->nodeFactory->createString($newValue);
                }
            }
        }

        return $classConstFetchNode;
    }

    private function getClassNameFromClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        /** @var FullyQualified|null $fqnName */
        $fqnName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($fqnName === null && $classConstFetchNode->class instanceof Variable) {
            return (string) $classConstFetchNode->class->name;
        }

        if ($fqnName !== null) {
            return $fqnName->toString();
        }
    }
}
