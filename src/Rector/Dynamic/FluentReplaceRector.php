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

/**
 * This Rector handles 2 things:
 * - removes "$return this;" method bodies
 * - changes fluent calls to standalone calls
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

    public function isCandidate(Node $node): bool
    {
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
            // method call to prepend
            $this->decoupleMethodCall($node);

            // move method call one up
            $node->name = $node->var->name;
            $node->var = $node->var->var;

            // to clear indent
            $node->setAttribute(Attribute::ORIGINAL_NODE, null);

            return $node;
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

    private function decoupleMethodCall(MethodCall $methodCallNode): void
    {
        $nextMethodCallNode = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $methodCallNode->var->var,
            $methodCallNode->name->toString()
        );

        $this->addNodeAfterNode($nextMethodCallNode, $methodCallNode->var);
    }
}
