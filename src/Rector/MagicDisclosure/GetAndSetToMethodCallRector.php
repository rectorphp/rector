<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\ExpressionAnalyzer;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetAndSetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var string[]
     */
    private $activeTransformation = [];

    /**
     * @var ExpressionAnalyzer
     */
    private $expressionAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    /**
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(
        array $typeToMethodCalls,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        ExpressionAnalyzer $expressionAnalyzer
    ) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->expressionAnalyzer = $expressionAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Turns defined __get/__set to specific method calls.', [
            new CodeSample('$someService = $container->someService;', '$container->getService("someService");'),
            new CodeSample('$container->someService = $someService;', '$container->setService("someService", $someService);')
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTransformation = [];

        $propertyFetchNode = $this->expressionAnalyzer->resolvePropertyFetch($node);
        if ($propertyFetchNode === null) {
            return false;
        }

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if ($this->propertyFetchAnalyzer->isMagicOnType($propertyFetchNode, $type)) {
                $this->activeTransformation = $transformation;

                return true;
            }
        }

        return false;
    }

    /**
     * @param Expression $expressionNode
     */
    public function refactor(Node $expressionNode): ?Node
    {
        /** @var Assign $assignNode */
        $assignNode = $expressionNode->expr;

        if ($assignNode->expr instanceof PropertyFetch) {
            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $assignNode->expr;
            $method = $this->activeTransformation['get'];
            $assignNode->expr = $this->createMethodCallNodeFromPropertyFetchNode($propertyFetchNode, $method);

            return $expressionNode;
        }

        /** @var Assign $assignNode */
        $assignNode = $expressionNode->expr;
        $method = $this->activeTransformation['set'];
        $expressionNode->expr = $this->createMethodCallNodeFromAssignNode($assignNode, $method);

        return $expressionNode;
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetchNode,
        string $method
    ): MethodCall {
        /** @var Identifier $identifierNode */
        $identifierNode = $propertyFetchNode->name;

        $value = $identifierNode->toString();

        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $variableNode,
            $method,
            [$value]
        );
    }

    private function createMethodCallNodeFromAssignNode(Assign $assignNode, string $method): MethodCall
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->var;

        /** @var Identifier $identifierNode */
        $identifierNode = $propertyFetchNode->name;

        $key = $identifierNode->toString();

        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        return $this->methodCallNodeFactory->createWithVariableMethodNameAndArguments(
            $variableNode,
            $method,
            [$key, $assignNode->expr]
        );
    }
}
