<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\Message\ClassPrepender;
use Rector\NodeValueResolver\NodeValueResolver;

final class FuncCallValueResolver implements PerNodeValueResolverInterface, NodeValueResolverAwareInterface
{
    /**
     * @var ClassPrepender
     */
    private $classPrepender;

    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function __construct(ClassPrepender $classPrepender)
    {
        $this->classPrepender = $classPrepender;
    }

    public function getNodeClass(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $funcCallArrayNode
     */
    public function resolve(Node $funcCallArrayNode)
    {
        if ((string) $funcCallArrayNode->name !== 'sprintf') {
            return null;
        }

        $message = $this->processSprintfNode($funcCallArrayNode);
        $message = $this->classPrepender->completeClassToLocalMethods(
            $message,
            (string) $funcCallArrayNode->getAttribute(Attribute::CLASS_NAME)
        );

        return $message ?: null;
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }

    private function processSprintfNode(FuncCall $funcCallNode): ?string
    {
        if ((string) $funcCallNode->name !== 'sprintf') {
            // or Exception?
            return null;
        }

        if ($this->isDynamicSprintf($funcCallNode)) {
            return null;
        }

        $arguments = $funcCallNode->args;
        $argumentCount = count($arguments);

        $firstArgument = $arguments[0]->value;
        if ($firstArgument instanceof String_) {
            $sprintfMessage = $firstArgument->value;
        } else {
            return null;
        }

        $sprintfArguments = [];
        for ($i = 1; $i < $argumentCount; ++$i) {
            $argument = $arguments[$i];
            $sprintfArguments[] = $this->nodeValueResolver->resolve($argument->value);
        }

        return sprintf($sprintfMessage, ...$sprintfArguments);
    }

    private function isDynamicSprintf(FuncCall $funcCallNode): bool
    {
        foreach ($funcCallNode->args as $argument) {
            if ($this->isDynamicArgument($argument)) {
                return true;
            }
        }

        return false;
    }

    private function isDynamicArgument(Arg $argument): bool
    {
        $valueNodeClass = get_class($argument->value);

        return in_array($valueNodeClass, [PropertyFetch::class, MethodCall::class, Variable::class], true);
    }
}
