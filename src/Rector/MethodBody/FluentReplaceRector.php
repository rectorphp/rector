<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(array $classesToDefluent = [])
    {
        $this->classesToDefluent = $classesToDefluent;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
                ,
                [
                    '$classesToDefluent' => ['SomeExampleClass'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isLastMethodCallInChainCall($node)) {
            return null;
        }

        $chainMethodCalls = $this->collectAllMethodCallsInChain($node);
        if (! $this->areChainMethodCallsMatching($chainMethodCalls)) {
            return null;
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls);

        $currentOne = array_pop($decoupledMethodCalls);

        // add separated method calls
        /** @var MethodCall[] $decoupledMethodCalls */
        $decoupledMethodCalls = array_reverse($decoupledMethodCalls);
        foreach ($decoupledMethodCalls as $decoupledMethodCall) {
            // needed to remove weird spacing
            $decoupledMethodCall->setAttribute('origNode', null);

            $this->addNodeAfterNode($decoupledMethodCall, $node);
        }

        return $currentOne;
    }

    private function isMatchingMethodCall(MethodCall $methodCall): bool
    {
        if ($this->isTypes($methodCall->var, $this->classesToDefluent)) {
            return true;
        }

        $type = $this->getTypes($methodCall)[0] ?? null;
        if ($type === null) {
            return false;
        }

        // asterisk * match
        foreach ($this->classesToDefluent as $classToDefluent) {
            if (! Strings::contains($classToDefluent, '*')) {
                continue;
            }

            if (fnmatch($classToDefluent, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MethodCall[] $methodCalls
     * @return Variable|PropertyFetch
     */
    private function extractRootVariable(array $methodCalls): Expr
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof Variable) {
                return $methodCall->var;
            }

            if ($methodCall->var instanceof PropertyFetch) {
                return $methodCall->var;
            }
        }

        throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
    }

    private function isLastMethodCallInChainCall(MethodCall $methodCall): bool
    {
        // is chain method call
        if (! $methodCall->var instanceof MethodCall) {
            return false;
        }

        $nextNode = $methodCall->getAttribute(AttributeKey::NEXT_NODE);

        // is last chain call
        return $nextNode === null;
    }

    /**
     * @return MethodCall[]
     */
    private function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];

        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        return $chainMethodCalls;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function areChainMethodCallsMatching(array $chainMethodCalls): bool
    {
        // is matching type all the way?
        foreach ($chainMethodCalls as $chainMethodCall) {
            if (! $this->isMatchingMethodCall($chainMethodCall)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return MethodCall[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls): array
    {
        $rootVariable = $this->extractRootVariable($chainMethodCalls);

        $decoupledMethodCalls = [];
        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCall->var = $rootVariable;
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        return $decoupledMethodCalls;
    }
}
