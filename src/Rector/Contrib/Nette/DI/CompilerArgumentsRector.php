<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use SplObjectStorage;

/**
 * Nette\DI\Compiler::compile arguments are deprecated, use Compiler::addConfig() and Compiler::setClassName().
 *
 * From:
 * - $compiler->compile($config, $className);
 *
 * To:
 * - $compiler->compile();
 * - $compiler->addConfig($config);
 * - $compiler->setClassName($className);
 */
final class CompilerArgumentsRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var SplObjectStorage|Expression[]
     */
    private $expressionsToPrepend = [];

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->expressionsToPrepend = new SplObjectStorage;

        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethodCallTypeAndMethod($node, 'Nette\DI\Compiler', 'compile')) {
            return false;
        }

        /** @var MethodCall $node */
        return count($node->args) >= 1;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        // statements with current key and prepends some
        $nodes = array_merge($nodes, $this->expressionsToPrepend);

        $this->expressionsToPrepend = [];

        return $nodes;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $oldArguments = $methodCallNode->args;

        $parentExpressionNode = $methodCallNode->getAttribute(Attribute::PARENT_NODE);

        $addConfigMethodCallNode = $this->cloneMethodWithNameAndArgument(
            $methodCallNode,
            'addConfig',
            $oldArguments[0]
        );
        $this->expressionsToPrepend[$parentExpressionNode] = new Expression($addConfigMethodCallNode);

        if (isset($oldArguments[1])) {
            $setClassNameMethodCallNode = $this->cloneMethodWithNameAndArgument(
                $methodCallNode,
                'setClassName',
                $oldArguments[1]
            );
            $this->expressionsToPrepend[$parentExpressionNode] = new Expression($setClassNameMethodCallNode);
        }

        $methodCallNode->args = [];

        return $methodCallNode;
    }

    private function cloneMethodWithNameAndArgument(
        MethodCall $methodCallNode,
        string $method,
        Arg $argNode
    ): MethodCall {
        $addConfigMethodCallNode = clone $methodCallNode;
        $addConfigMethodCallNode->name = $method;
        $addConfigMethodCallNode->args = [$argNode];

        return $addConfigMethodCallNode;
    }
}
