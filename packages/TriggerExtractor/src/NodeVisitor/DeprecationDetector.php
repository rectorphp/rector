<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\NodeVisitor;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\TriggerExtractor\Deprecation\DeprecationCollector;
use Rector\TriggerExtractor\TriggerError\TriggerMessageResolver;

final class DeprecationDetector extends NodeVisitorAbstract // @todo use : class aware node visitor
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var TriggerMessageResolver
     */
    private $triggerMessageResolver;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        TriggerMessageResolver $triggerMessageResolver
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->triggerMessageResolver = $triggerMessageResolver;
    }

    public function enterNode(Node $node): void
    {
        if (! $this->isTriggerErrorUserDeprecated($node)) {
            return;
        }

        /** @var FuncCall $node */
        $deprecation = $this->triggerMessageResolver->resolve($node->args[0]->value);

        $this->deprecationCollector->addDeprecation($deprecation);
    }

    /**
     * This detects: "trigger_error(<some-content>, E_USER_DEPREDCATED)";
     */
    private function isTriggerErrorUserDeprecated(Node $node): bool
    {
        if (! $this->isFunctionWithName($node, 'trigger_error')) {
            return false;
        }

        /** @var FuncCall $node */
        if (count($node->args) !== 2) {
            return false;
        }

        /** @var Arg $secondArgumentNode */
        $secondArgumentNode = $node->args[1];
        if (! $secondArgumentNode->value instanceof ConstFetch) {
            return false;
        }

        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $secondArgumentNode->value;

        return $constFetchNode->name->toString() === 'E_USER_DEPRECATED';
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
