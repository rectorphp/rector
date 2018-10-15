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
use Rector\RectorDefinition\ConfiguredCodeSample;
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
        return new RectorDefinition('Turns defined `__get`/`__set` to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->someService = $someService;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->setService("someService", $someService);
CODE_SAMPLE
                ,
                [
                    '$typeToMethodCalls' => [
                        'SomeContainer' => [
                            'set' => 'addService',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->someService;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$someService = $container->getService("someService");
CODE_SAMPLE
                ,
                [
                    '$typeToMethodCalls' => [
                        'SomeContainer' => [
                            'get' => 'getService',
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
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        $activeTransformation = null;
        $propertyFetchNode = $this->expressionAnalyzer->resolvePropertyFetch($node);
        if ($propertyFetchNode === null) {
            return null;
        }

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if ($this->propertyFetchAnalyzer->isMagicOnType($propertyFetchNode, $type)) {
                $activeTransformation = $transformation;
                break;
            }
        }

        if ($activeTransformation === null) {
            return null;
        }

        /** @var Assign $assignNode */
        $assignNode = $node->expr;

        if ($assignNode->expr instanceof PropertyFetch) {
            /** @var PropertyFetch $propertyFetchNode */
            $propertyFetchNode = $assignNode->expr;
            $method = $activeTransformation['get'];
            $assignNode->expr = $this->createMethodCallNodeFromPropertyFetchNode($propertyFetchNode, $method);

            return $node;
        }

        /** @var Assign $assignNode */
        $assignNode = $node->expr;
        $method = $activeTransformation['set'];
        $node->expr = $this->createMethodCallNodeFromAssignNode($assignNode, $method);

        return $node;
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
