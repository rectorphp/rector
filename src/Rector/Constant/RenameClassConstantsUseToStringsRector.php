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
     * @var string
     */
    private $class;

    /**
     * @var string[]
     */
    private $oldConstantToNewValue = [];

    /**
     * @param string[] $oldConstantToNewValue
     */
    public function __construct(NodeFactory $nodeFactory, string $class, array $oldConstantToNewValue)
    {
        $this->nodeFactory = $nodeFactory;
        $this->class = $class;
        $this->oldConstantToNewValue = $oldConstantToNewValue;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces constant by value', [
            new ConfiguredCodeSample(
                '$value === Nette\Configurator::DEVELOPMENT',
                '$value === "development"',
                [
                    '$class' => 'Nette\Configurator',
                    '$oldConstantToNewValue' => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
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
        if (! $classConstFetchNode instanceof ClassConstFetch) {
            return null;
        }
        $className = $this->getClassNameFromClassConstFetch($classConstFetchNode);
        if ($className !== $this->class) {
            return null;
        }
        if (array_key_exists((string) $classConstFetchNode->name, $this->oldConstantToNewValue) === false) {
            return null;
        }
        $newValue = $this->oldConstantToNewValue[(string) $classConstFetchNode->name];

        return $this->nodeFactory->createString($newValue);
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
