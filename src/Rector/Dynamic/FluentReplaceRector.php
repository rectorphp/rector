<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Inspiration:
 * - https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * - http://www.yegor256.com/2018/03/13/fluent-interfaces.html
 */
final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $relatedTypesAndMethods = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Turns fluent interfaces to classic ones.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
    class SomeClass
    {
        public function someFunction()
        {
            return $this;
        }
    
        public function otherFunction()
        {
            return $this;
        }
    }
        
    $someClass = new SomeClass();
    $someClass->someFunction()
                ->otherFunction();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
    class SomeClass
    {
        public function someFunction()
        {
        }
    
        public function otherFunction()
        {
        }
    }
    
    $someClass = new SomeClass();
    $someClass->someFunction();
    $someClass->otherFunction();
CODE_SAMPLE
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        // @todo this run has to be first, dual run?
        if ($node instanceof Return_) {
            if (! $node->expr instanceof Variable) {
                return false;
            }

            return $node->expr->name === 'this';
        }

        if ($node instanceof MethodCall) {
            return $this->isMethodCallCandidate($node);
        }

        return false;
    }

    /**
     * @param Return_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Return_) {
            $this->removeNode = true;

            $className = $node->getAttribute(Attribute::CLASS_NAME);
            $methodName = $node->getAttribute(Attribute::METHOD_NAME);

            $this->relatedTypesAndMethods[$className][] = $methodName;

            return null;
        }

        if ($node instanceof MethodCall) {
            /** @var MethodCall $innerMethodCallNode */
            $innerMethodCallNode = $node->var;

            $this->decoupleMethodCall($node, $innerMethodCallNode);

            return $innerMethodCallNode;
        }

        return $node;
    }

    private function isMethodCallCandidate(MethodCall $methodCallNode): bool
    {
        // is chain method call
        if (! $methodCallNode->var instanceof MethodCall) {
            return false;
        }

        foreach ($this->relatedTypesAndMethods as $type => $methods) {
            if (! $this->methodCallAnalyzer->isTypeAndMethods($methodCallNode->var, $type, $methods)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function decoupleMethodCall(MethodCall $outerMethodCallNode, MethodCall $innerMethodCallNode): void
    {
        $nextMethodCallNode = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $innerMethodCallNode->var,
            $outerMethodCallNode->name->toString()
        );

        $this->addNodeAfterNode($nextMethodCallNode, $innerMethodCallNode);
    }
}
