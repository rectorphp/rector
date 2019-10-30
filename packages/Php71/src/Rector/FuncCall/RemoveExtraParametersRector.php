<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
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
 *
 * @see \Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var ReflectionFunction $reflectionFunctionLike */
        $reflectionFunctionLike = $this->resolveReflectionFunctionLike($node);

        $numberOfParameters = $reflectionFunctionLike->getNumberOfParameters();
        $numberOfArguments = count($node->args);

        for ($i = $numberOfParameters; $i <= $numberOfArguments; $i++) {
            unset($node->args[$i]);
        }

        return $node;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if (count($node->args) === 0) {
            return true;
        }

        if ($node instanceof StaticCall) {
            if ($this->isName($node->class, 'parent')) {
                return true;
            }
        }

        $reflectionFunctionLike = $this->resolveReflectionFunctionLike($node);
        if ($reflectionFunctionLike === null) {
            return true;
        }

        // can be any number of arguments → nothing to limit here
        if ($this->callManipulator->isVariadic($reflectionFunctionLike, $node)) {
            return true;
        }
        return $reflectionFunctionLike->getNumberOfParameters() >= count($node->args);
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
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

        if ($node instanceof StaticCall) {
            $objectType = $this->getObjectType($node->class);
        } elseif ($node instanceof MethodCall) {
            $objectType = $this->getObjectType($node->var);
        } else {
            return null;
        }

        // unable to resolve
        if (! $objectType instanceof ObjectType && ! $objectType instanceof UnionType) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        $class = $this->resolveClassOverIntefaceFromType($objectType);
        if ($class === null) {
            return null;
        }

        if (! method_exists($class, $methodName)) {
            return null;
        }

        return new ReflectionMethod($class, $methodName);
    }

    /**
     * Give class priority over interface, because it can change the interface signature
     * @see https://github.com/rectorphp/rector/issues/1593#issuecomment-502404580
     */
    private function resolveClassOverIntefaceFromType(Type $type): ?string
    {
        $classNames = TypeUtils::getDirectClassNames($type);
        foreach ($classNames as $className) {
            if (class_exists($className)) {
                return $className;
            }
        }

        return null;
    }
}
