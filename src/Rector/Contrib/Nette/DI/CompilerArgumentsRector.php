<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Builder\StatementGlue;
use Rector\Node\Attribute;
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

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, StatementGlue $statementGlue)
    {
        $this->expressionsToPrepend = new SplObjectStorage;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->statementGlue = $statementGlue;
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
     * @todo build into abstract?
     *
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        return $this->processNodes($nodes);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $oldArguments = $methodCallNode->args;
        $nodesToPrepend = [];

        $addConfigMethodCallNode = $this->cloneMethodWithNameAndArgument(
            $methodCallNode,
            'addConfig',
            $oldArguments[0]
        );
        $nodesToPrepend[] = new Expression($addConfigMethodCallNode);

        if (isset($oldArguments[1])) {
            $setClassNameMethodCallNode = $this->cloneMethodWithNameAndArgument(
                $methodCallNode,
                'setClassName',
                $oldArguments[1]
            );
            $nodesToPrepend[] = new Expression($setClassNameMethodCallNode);
        }

        $parentExpressionNode = $methodCallNode->getAttribute(Attribute::PARENT_NODE);
        $this->expressionsToPrepend[$parentExpressionNode] = $nodesToPrepend;

        $methodCallNode->args = [];

        return $methodCallNode;
    }

    /**
     * @param Node[] $nodes
     * @return Node[] array
     */
    private function processNodes(array $nodes): array
    {
        foreach ($nodes as $i => $node) {
            if ($node instanceof Expression) {
                if (! isset($this->expressionsToPrepend[$node])) {
                    continue;
                }

                $nodes = $this->statementGlue->insertNewNodesAfter($nodes, $this->expressionsToPrepend[$node], $i);
                $this->expressionsToPrepend[$node] = null;
            } elseif (isset($node->stmts)) {
                $node->stmts = $this->processNodes($node->stmts);
            }
        }

        return $nodes;
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
