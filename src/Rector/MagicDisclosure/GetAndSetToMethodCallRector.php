<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
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
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->expr instanceof PropertyFetch) {
            return $this->processMagicGet($node);
        }

        if ($node->var instanceof PropertyFetch) {
            return $this->processMagicSet($node);
        }

        return null;
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetchNode,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetchNode)]);
    }

    private function createMethodCallNodeFromAssignNode(
        PropertyFetch $propertyFetchNode,
        Node $node,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetchNode), $node]);
    }

    private function processMagicGet(Assign $assignNode): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->expr;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->propertyFetchAnalyzer->isMagicOnType($propertyFetchNode, $type)) {
                continue;
            }

            $assignNode->expr = $this->createMethodCallNodeFromPropertyFetchNode(
                $propertyFetchNode,
                $transformation['get']
            );

            return $assignNode;
        }

        return null;
    }

    private function processMagicSet(Assign $assignNode): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assignNode->var;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->propertyFetchAnalyzer->isMagicOnType($propertyFetchNode, $type)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode(
                $propertyFetchNode,
                $assignNode->expr,
                $transformation['set']
            );
        }

        return null;
    }
}
