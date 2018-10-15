<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ToStringToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var string
     */
    private $activeTransformation;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

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
        IdentifierRenamer $identifierRenamer,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->identifierRenamer = $identifierRenamer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined code uses of "__toString()" method  to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = (string) $someValue;
$result = $someValue->__toString();
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = $someValue->someMethod();
$result = $someValue->someMethod();
CODE_SAMPLE
                ,
                [
                    '$typeToMethodCalls' => [
                        'SomeObject' => [
                            'toString' => 'getPath',
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
        return [String_::class, MethodCall::class];
    }

    /**
     * @param String_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof String_ && $this->processStringCandidate($node)) {
            return $this->methodCallNodeFactory->createWithVariableAndMethodName(
                $node->expr,
                $this->activeTransformation
            );
        }

        if ($node instanceof MethodCall && $this->processMethodCallCandidate($node)) {
            $this->identifierRenamer->renameNode($node, $this->activeTransformation);
        }

        return $node;
    }

    private function processStringCandidate(String_ $stringNode): bool
    {
        $nodeTypes = $this->getTypes($stringNode->expr);

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (in_array($type, $nodeTypes, true)) {
                $this->activeTransformation = $transformation['toString'];

                return true;
            }
        }

        return false;
    }

    private function processMethodCallCandidate(MethodCall $methodCallNode): bool
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (! $this->isType($methodCallNode, $type)) {
                continue;
            }

            if (! $this->isName($methodCallNode, '__toString')) {
                continue;
            }

            $this->activeTransformation = $transformation['toString'];

            return true;
        }

        return false;
    }
}
