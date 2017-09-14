<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\NodeValueResolverAwareInterface;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;
use Rector\NodeValueResolver\Message\ClassPrepender;
use Rector\NodeValueResolver\NodeAnalyzer\DynamicNodeAnalyzer;
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
    /**
     * @var DynamicNodeAnalyzer
     */
    private $dynamicNodeAnalyzer;

    public function __construct(ClassPrepender $classPrepender, DynamicNodeAnalyzer $dynamicNodeAnalyzer)
    {
        $this->classPrepender = $classPrepender;
        $this->dynamicNodeAnalyzer = $dynamicNodeAnalyzer;
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

        if ($message === null) {
            return null;
        }

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

        if ($this->dynamicNodeAnalyzer->hasDynamicNodes($funcCallNode->args)) {
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
}
