<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * @see https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @see http://php.net/manual/en/class.argumentcounterror.php
 */
final class RemoveExtraParametersRector extends AbstractRector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var FunctionLikeNodeCollector
     */
    private $functionLikeNodeCollector;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        FunctionLikeNodeCollector $functionLikeNodeCollector
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->functionLikeNodeCollector = $functionLikeNodeCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove extra parameters', [
            new CodeSample('strlen("asdf", 1);', 'strlen("asdf");'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->args) === 0) {
            return null;
        }

        $reflectionFunctionLike = $this->resolveReflectionFunctionLike($node);
        if ($reflectionFunctionLike === null) {
            return null;
        }

        // can be any number of arguments → nothing to limit here
        if ($this->isVariadic($reflectionFunctionLike)) {
            return null;
        }

        if ($reflectionFunctionLike->getNumberOfParameters() >= count($node->args)) {
            return null;
        }

        for ($i = $reflectionFunctionLike->getNumberOfParameters(); $i < count($node->args); $i++) {
            unset($node->args[$i]);
        }

        // re-index for printer
        $node->args = array_values($node->args);

        return $node;
    }

    private function resolveReflectionFunctionLike(Node $node): ?ReflectionFunctionAbstract
    {
        if ($node instanceof FuncCall) {
            $functionName = $this->getName($node);
            if ($functionName === null) {
                return null;
            }

            return new ReflectionFunction($functionName);
        }

        $nodeTypes = $this->getTypes($node);

        // unable to resolve
        if (!isset($nodeTypes[0])) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        return new ReflectionMethod($nodeTypes[0], $methodName);
    }

    /**
     * @param ReflectionMethod|ReflectionFunction $reflectionFunctionAbstract
     */
    private function isVariadic(ReflectionFunctionAbstract $reflectionFunctionAbstract): bool
    {
        // detects from ... in parameters
        if ($reflectionFunctionAbstract->isVariadic()) {
            return true;
        }

        if ($reflectionFunctionAbstract instanceof ReflectionFunction) {
            $functionNode = $this->functionLikeNodeCollector->findFunction($reflectionFunctionAbstract->getName());
            if ($functionNode === null) {
                return false;
            }

            return $this->hasFunctionLikeVariadicFuncCall($functionNode);
        }

        $classMethodNode = $this->functionLikeNodeCollector->findMethod(
            $reflectionFunctionAbstract->getName(),
            $reflectionFunctionAbstract->getDeclaringClass()->getName()
        );
        if ($classMethodNode === null) {
            return false;
        }

        return $this->hasFunctionLikeVariadicFuncCall($classMethodNode);

        // @todo external function/method; $reflectionFunctionAbstract->getFileName() === false
    }

    private function hasFunctionLikeVariadicFuncCall(FunctionLike $functionLikeNode): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($functionLikeNode, function (Node $node) {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if ($this->isNames($node, ['func_get_args', 'func_get_arg', 'func_num_args'])) {
                return true;
            }

            return null;
        });
    }
}
