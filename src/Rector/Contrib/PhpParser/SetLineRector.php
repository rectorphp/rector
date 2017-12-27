<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $node->setLine(5);
 *
 * After:
 * - $node->setAttribute('line', 5);
 */
final class SetLineRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $IdentifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $IdentifierRenamer;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypeAndMethod($node, 'PhpParser\Node', 'setLine');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNode($methodCallNode, 'setAttribute');

        $methodCallNode->args[1] = $methodCallNode->args[0];
        $methodCallNode->args[0] = new Arg(new String_('line'));

        return $methodCallNode;
    }
}
