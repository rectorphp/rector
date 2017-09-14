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
     * @param FuncCall $funcCallNode
     */
    public function resolve(Node $funcCallNode)
    {
        $message = '';

        if ((string) $funcCallNode->name === 'sprintf') {
            $message = $this->processSprintfNode($funcCallNode);
            $message = $this->classPrepender->completeClassToLocalMethods(
                $message,
                (string) $funcCallNode->getAttribute(Attribute::CLASS_NAME)
            );
        }

        if ($message === '') {
            return null;
        }

        dump($funcCallNode);
        die;
    }

    public function setNodeValueResolver(NodeValueResolver $nodeValueResolver): void
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }

    private function processSprintfNode(FuncCall $funcCallNode): string
    {
        if ((string) $funcCallNode->name !== 'sprintf') {
            // or Exception?
            return '';
        }

        if ($this->isDynamicSprintf($funcCallNode)) {
            return '';
        }

        $arguments = $funcCallNode->args;
        $argumentCount = count($arguments);

        $firstArgument = $arguments[0]->value;
        if ($firstArgument instanceof String_) {
            $sprintfMessage = $firstArgument->value;
        }

        $sprintfArguments = [];
        for ($i = 1; $i < $argumentCount; ++$i) {
            $argument = $arguments[$i];

            $sprintfArguments[] = $this->nodeValueResolver->resolve($argument->value);

//            if ($argument->value instanceof Method) {
//                /** @var Node\Stmt\ClassMethod $methodNode */
//                $methodNode = $funcCallNode->getAttribute(Attribute::SCOPE_NODE);
//                $sprintfArguments[] = (string) $methodNode->name;
//            } elseif ($argument->value instanceof ClassConstFetch) {
//                $value = $this->standardPrinter->prettyPrint([$argument->value]);
//                if ($value === 'static::class') {
//                    $sprintfArguments[] = $argument->value->getAttribute(Attribute::CLASS_NAME);
//                }
//            } else {
//                dump($this->standardPrinter->prettyPrint([$argument]));
//                die;
//
//                throw new NotImplementedException(sprintf(
//                    'Not implemented yet. Go to "%s()" and add check for "%s" argument node.',
//                    __METHOD__,
//                    get_class($argument->value)
//                ));
//            }
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
