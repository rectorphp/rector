<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * From:
 * - $compiler->generateCode($className);
 *
 * To:
 * - $compiler->setClassName($className);
 * - $compiler->generateCode();
 */
final class CompilerGenerateCodeArgumentsRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethodCallTypeAndMethod($node, 'Nette\DI\Compiler', 'generateCode')) {
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
        $oldArguments = $methodCallNode->args;

        $setClassNameMethodCallNode = $this->cloneMethodWithNameAndArgument(
            $methodCallNode,
            'setClassName',
            $oldArguments[0]
        );

        $this->prependNodeBeforeNode($setClassNameMethodCallNode, $methodCallNode);

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
