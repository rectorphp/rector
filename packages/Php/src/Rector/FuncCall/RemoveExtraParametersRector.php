<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PhpParser\Node\Maintainer\CallMaintainer;
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
     * @var CallMaintainer
     */
    private $callMaintainer;

    public function __construct(CallMaintainer $callMaintainer)
    {
        $this->callMaintainer = $callMaintainer;
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
        if ($this->callMaintainer->isVariadic($reflectionFunctionLike, $node)) {
            return null;
        }

        if ($reflectionFunctionLike->getNumberOfParameters() >= count($node->args)) {
            return null;
        }

        for ($i = $reflectionFunctionLike->getNumberOfParameters(); $i <= count($node->args); $i++) {
            unset($node->args[$i]);
        }

        return $node;
    }

    /**
     * @return ReflectionFunction|ReflectionMethod
     */
    private function resolveReflectionFunctionLike(Node $node): ?ReflectionFunctionAbstract
    {
        if ($node instanceof FuncCall) {
            $functionName = $this->getName($node);
            if ($functionName === null) {
                return null;
            }

            if (! function_exists($functionName)) {
                return null;
            }

            return new ReflectionFunction($functionName);
        }

        $nodeTypes = $this->getTypes($node);

        // unable to resolve
        if (! isset($nodeTypes[0])) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        if (! method_exists($nodeTypes[0], $methodName)) {
            return null;
        }

        return new ReflectionMethod($nodeTypes[0], $methodName);
    }
}
