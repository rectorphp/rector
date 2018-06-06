<?php declare(strict_types=1);

namespace Rector\Nette\Rector\Bootstrap;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveConfiguratorConstantsRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var string
     */
    private $oldClassConstant;

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
    public function __construct(
        NodeFactory $nodeFactory,
        string $class = 'Nette\Configurator',
        array $oldConstantToNewValue = [
            'DEVELOPMENT' => 'development',
            'PRODUCTION' => 'production'
        ]
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->class = $class;
        $this->oldConstantToNewValue = $oldConstantToNewValue;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces constant by value', [
            new CodeSample('$value === Nette\Configurator::DEVELOPMENT', '$value === "development"'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        $className = $this->getClassNameFromClassConstFetch($node);

        if ($className !== $this->class) {
            return false;
        }

        return in_array((string) $node->name, array_keys($this->oldConstantToNewValue), true);
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor(Node $classConstFetchNode): ?Node
    {
        $newValue = $this->oldConstantToNewValue[$classConstFetchNode->name->toString()];

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
