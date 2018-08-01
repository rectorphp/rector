<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyToMethodRector extends AbstractRector
{
    /**
     * class => [
     *     property => [getMethod, setMethod]
     * ]
     *
     * @var string[][][]
     */
    private $perClassPropertyToMethods = [];

    /**
     * @var string
     */
    private $activeMethod;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * @param string[][][] $perClassPropertyToMethods
     */
    public function __construct(
        array $perClassPropertyToMethods,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        NodeFactory $nodeFactory,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->perClassPropertyToMethods = $perClassPropertyToMethods;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces properties assign calls be defined methods.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$result = $object->property;
$object->property = $value;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$result = $object->getProperty();
$object->setProperty($value);
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        // setter
        if ($node->var instanceof PropertyFetch) {
            return $this->processPropertyFetchCandidate($node->var, 'set');
        }

        // getter
        if ($node->expr instanceof PropertyFetch) {
            return $this->processPropertyFetchCandidate($node->expr, 'get');
        }

        return false;
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        // setter
        if ($assignNode->var instanceof PropertyFetch) {
            $args = $this->nodeFactory->createArgs([$assignNode->expr]);

            /** @var Variable $variable */
            $variable = $assignNode->var->var;

            return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
                $variable,
                $this->activeMethod,
                $args
            );
        }

        // getter
        if ($assignNode->expr instanceof PropertyFetch) {
            $assignNode->expr = $this->methodCallNodeFactory->createWithVariableAndMethodName(
                $assignNode->expr->var,
                $this->activeMethod
            );
        }

        return null;
    }

    private function processPropertyFetchCandidate(PropertyFetch $propertyFetchNode, string $type): bool
    {
        foreach ($this->perClassPropertyToMethods as $class => $propertyToMethods) {
            $properties = array_keys($propertyToMethods);
            if ($this->propertyFetchAnalyzer->isTypeAndProperties($propertyFetchNode, $class, $properties)) {
                /** @var Identifier $identifierNode */
                $identifierNode = $propertyFetchNode->name;

                $this->activeMethod = $propertyToMethods[$identifierNode->toString()][$type];

                return true;
            }
        }

        return false;
    }
}
