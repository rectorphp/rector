<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\PhpParser\Node\Manipulator\PropertyFetchManipulator;
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
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls, PropertyFetchManipulator $propertyFetchManipulator)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
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
                    'SomeContainer' => [
                        'set' => 'addService',
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
        return [Assign::class, Node\Stmt\If_::class];
    }

    /**
     * @param Assign|Node\Stmt\If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->expr instanceof PropertyFetch) {
                return $this->processMagicGet($node);
            }

            if ($node->var instanceof PropertyFetch) {
                return $this->processMagicSet($node);
            }
        }

        if ($node instanceof Node\Stmt\If_ && $node->cond instanceof PropertyFetch) {
            return $this->processMagicGetOnIf($node);
        }


        return null;
    }

    private function processMagicGet(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->expr;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->propertyFetchManipulator->isMagicOnType($propertyFetchNode, $type)) {
                continue;
            }

            $assign->expr = $this->createMethodCallNodeFromPropertyFetchNode(
                $propertyFetchNode,
                $transformation['get']
            );

            return $assign;
        }

        return null;
    }

    private function processMagicSet(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->propertyFetchManipulator->isMagicOnType($propertyFetchNode, $type)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode(
                $propertyFetchNode,
                $assign->expr,
                $transformation['set']
            );
        }

        return null;
    }

    private function processMagicGetOnIf(Node\Stmt\If_ $if): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $if->cond;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($propertyFetchNode, $type)) {
                continue;
            }

            if (! $this->propertyFetchManipulator->isMagicOnType($propertyFetchNode, $type)) {
                continue;
            }

            $if->cond = $this->createMethodCallNodeFromPropertyFetchNode(
                $propertyFetchNode,
                $transformation['get']
            );

            return $if;
        }

        return null;
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetch,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }

    private function createMethodCallNodeFromAssignNode(
        PropertyFetch $propertyFetch,
        Node $node,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch), $node]);
    }
}
