<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    public function __construct(
        private SpreadVariablesCollector $spreadVariablesCollector,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove spread operator', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(...$array)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $array)
    {
    }

    public function execute(array $data)
    {
        $this->run($data);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->processUnspreadOperatorClassMethodParams($node);
        }

        return $this->processUnspreadOperatorMethodCallArgs($node);
    }

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        $spreadParams = $this->spreadVariablesCollector->resolveFromClassMethod($classMethod);
        if ($spreadParams === []) {
            return null;
        }

        foreach ($spreadParams as $spreadParam) {
            $spreadParam->variadic = false;
            $spreadParam->type = new Identifier('array');
            $spreadParam->default = $this->nodeFactory->createArray([]);
        }

        return $classMethod;
    }

    private function processUnspreadOperatorMethodCallArgs(MethodCall $methodCall): ?MethodCall
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (! $methodReflection instanceof MethodReflection) {
            return null;
        }

        // skip those in vendor
        if ($this->skipForVendor($methodReflection)) {
            return null;
        }

        $spreadParameterReflections = $this->spreadVariablesCollector->resolveFromMethodReflection(
            $methodReflection
        );

        if ($spreadParameterReflections === []) {
            return null;
        }

        $firstSpreadParamPosition = array_key_first($spreadParameterReflections);
        $variadicArgs = $this->resolveVariadicArgsByVariadicParams($methodCall, $firstSpreadParamPosition);

        $hasUnpacked = $this->changeArgToPacked($variadicArgs, $methodCall);
        if ($hasUnpacked) {
            return $methodCall;
        }

        if ($variadicArgs !== []) {
            $array = $this->nodeFactory->createArray($variadicArgs);

            $spreadArg = $methodCall->args[$firstSpreadParamPosition] ?? null;

            // already set value
            if ($spreadArg instanceof Arg && $spreadArg->value instanceof Array_) {
                return null;
            }

            if (count($variadicArgs) === 1) {
                return null;
            }

            $methodCall->args[$firstSpreadParamPosition] = new Arg($array);
            $this->removeLaterArguments($methodCall, $firstSpreadParamPosition);

            return $methodCall;
        }

        return null;
    }

    /**
     * @return Arg[]
     */
    private function resolveVariadicArgsByVariadicParams(MethodCall $methodCall, int $firstSpreadParamPosition): array
    {
        $variadicArgs = [];

        foreach ($methodCall->args as $position => $arg) {
            if ($position < $firstSpreadParamPosition) {
                continue;
            }

            $variadicArgs[] = $arg;
        }

        return $variadicArgs;
    }

    private function skipForVendor(MethodReflection | FunctionReflection $functionLikeReflection): bool
    {
        if ($functionLikeReflection instanceof PhpFunctionReflection) {
            $fileName = $functionLikeReflection->getFileName();
        } elseif ($functionLikeReflection instanceof MethodReflection) {
            $declaringClassReflection = $functionLikeReflection->getDeclaringClass();
            $fileName = $declaringClassReflection->getFileName();
        } else {
            return false;
        }

        // probably internal
        if ($fileName === false) {
            return true;
        }

        return str_contains($fileName, '/vendor/');
    }

    private function removeLaterArguments(MethodCall $methodCall, int $argumentPosition): void
    {
        $argCount = count($methodCall->args);
        for ($i = $argumentPosition + 1; $i < $argCount; ++$i) {
            unset($methodCall->args[$i]);
        }
    }

    /**
     * @param Arg[] $variadicArgs
     */
    private function changeArgToPacked(array $variadicArgs, MethodCall $methodCall): bool
    {
        $hasUnpacked = false;

        foreach ($variadicArgs as $position => $variadicArg) {
            if ($variadicArg->unpack) {
                $variadicArg->unpack = false;
                $hasUnpacked = true;
                $methodCall->args[$position] = $variadicArg;
            }
        }

        return $hasUnpacked;
    }
}
