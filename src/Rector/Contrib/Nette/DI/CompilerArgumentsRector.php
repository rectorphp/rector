<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

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

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
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
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->args = [];



//        $methodCallNode->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $methodCallNode;
    }
}
