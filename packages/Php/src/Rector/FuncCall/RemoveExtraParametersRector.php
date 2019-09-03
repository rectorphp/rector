<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PhpParser\Node\Manipulator\CallManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * @see https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @see http://php.net/manual/en/class.argumentcounterror.php
 * @see \Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
 */
final class RemoveExtraParametersRector extends AbstractRector
{
    /**
     * @var CallManipulator
     */
    private $callManipulator;

    public function __construct(CallManipulator $callManipulator)
    {
        $this->callManipulator = $callManipulator;
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
        if ($this->callManipulator->isVariadic($reflectionFunctionLike, $node)) {
            return null;
        }

        if ($reflectionFunctionLike->getNumberOfParameters() >= count($node->args)) {
            return null;
        }

        for ($i = $reflectionFunctionLike->getNumberOfParameters(), $iMax = count($node->args); $i <= $iMax; $i++) {
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

        $priorityType = $this->resolvePriorityType($nodeTypes);
        if ($priorityType === null) {
            return null;
        }

        if (! method_exists($priorityType, $methodName)) {
            return null;
        }

        return new ReflectionMethod($priorityType, $methodName);
    }

    /**
     * Give class priority, because it can change the interface signature,
     * @see https://github.com/rectorphp/rector/issues/1593#issuecomment-502404580
     *
     * @param string[] $nodeTypes
     */
    private function resolvePriorityType(array $nodeTypes): ?string
    {
        $priorityType = null;
        foreach ($nodeTypes as $nodeType) {
            if (class_exists($nodeType)) {
                $priorityType = $nodeType;
                break;
            }

            $priorityType = $nodeType;
        }

        return $priorityType;
    }
}
