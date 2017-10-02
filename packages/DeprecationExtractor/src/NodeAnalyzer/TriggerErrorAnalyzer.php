<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use SplObjectStorage;

final class TriggerErrorAnalyzer
{
    /**
     * @var SplObjectStorage|Arg[]
     */
    private $messageNodePerTriggerErrorNode = [];

    public function __construct()
    {
        $this->messageNodePerTriggerErrorNode = new SplObjectStorage;
    }

    public function isUserDeprecation(Node $node): bool
    {
        if (! $this->isFunctionWithName($node, 'trigger_error')) {
            return false;
        }

        /** @var FuncCall $node */
        if (count($node->args) !== 2) {
            return false;
        }

        $this->messageNodePerTriggerErrorNode[$node] = $node->args[0];

        /** @var Arg $secondArgumentNode */
        $secondArgumentNode = $node->args[1];
        if (! $secondArgumentNode->value instanceof ConstFetch) {
            return false;
        }

        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $secondArgumentNode->value;

        return $constFetchNode->name->toString() === 'E_USER_DEPRECATED';
    }

    public function messageNodeForNode(FuncCall $triggerErrorFuncCallNode): Arg
    {
        return $this->messageNodePerTriggerErrorNode[$triggerErrorFuncCallNode];
    }

    private function isFunctionWithName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }

        return $node->name->toString() === $name;
    }
}
