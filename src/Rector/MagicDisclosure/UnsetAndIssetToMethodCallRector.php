<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Unset_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UnsetAndIssetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined `__isset`/`__unset` calls to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$container = new SomeContainer;
isset($container["someKey"]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->hasService("someKey");
CODE_SAMPLE
                ,
                [
                    '$typeToMethodCalls' => [
                        'SomeContainer' => [
                            'isset' => 'hasService',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
unset($container["someKey"]);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->removeService("someKey");
CODE_SAMPLE
                ,
                [
                    [
                        '$typeToMethodCalls' => [
                            'SomeContainer' => [
                                'unset' => 'removeService',
                            ],
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
        return [Isset_::class, Unset_::class];
    }

    /**
     * @param Isset_|Unset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->vars as $arrayDimFetchNode) {
            if (! $arrayDimFetchNode instanceof ArrayDimFetch) {
                continue;
            }

            foreach ($this->typeToMethodCalls as $type => $transformation) {
                if (! $this->isType($arrayDimFetchNode, $type)) {
                    continue;
                }

                $newNode = $this->processArrayDimFetchNode($node, $arrayDimFetchNode, $transformation);
                if ($newNode) {
                    return $newNode;
                }
            }
        }

        return null;
    }

    /**
     * @param string[] $methodsNamesByType
     */
    private function processArrayDimFetchNode(
        Node $node,
        ArrayDimFetch $arrayDimFetchNode,
        array $methodsNamesByType
    ): ?Node {
        if ($node instanceof Isset_) {
            if (! isset($methodsNamesByType['isset'])) {
                return null;
            }

            return $this->createMethodCall(
                $arrayDimFetchNode->var,
                $methodsNamesByType['isset'],
                [$arrayDimFetchNode->dim]
            );
        }

        if ($node instanceof Unset_) {
            if (! isset($methodsNamesByType['unset'])) {
                return null;
            }

            $methodCall = $this->createMethodCall(
                $arrayDimFetchNode->var,
                $methodsNamesByType['unset'],
                [$arrayDimFetchNode->dim]
            );
            // wrap it, so add ";" in the end of line
            return new Expression($methodCall);
        }
    }
}
