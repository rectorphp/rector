<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector
     */
    private $spreadVariablesCollector;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\CodingStyle\Reflection\VendorLocationDetector
     */
    private $vendorLocationDetector;
    public function __construct(\Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector $spreadVariablesCollector, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\CodingStyle\Reflection\VendorLocationDetector $vendorLocationDetector)
    {
        $this->spreadVariablesCollector = $spreadVariablesCollector;
        $this->reflectionResolver = $reflectionResolver;
        $this->vendorLocationDetector = $vendorLocationDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove spread operator', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->processUnspreadOperatorClassMethodParams($node);
        }
        return $this->processUnspreadOperatorMethodCallArgs($node);
    }
    private function processUnspreadOperatorClassMethodParams(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $spreadParams = $this->spreadVariablesCollector->resolveFromClassMethod($classMethod);
        if ($spreadParams === []) {
            return null;
        }
        foreach ($spreadParams as $spreadParam) {
            $spreadParam->variadic = \false;
            $spreadParam->type = new \PhpParser\Node\Identifier('array');
            $spreadParam->default = $this->nodeFactory->createArray([]);
        }
        return $classMethod;
    }
    private function processUnspreadOperatorMethodCallArgs(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        // skip those in vendor
        if ($this->vendorLocationDetector->detectFunctionLikeReflection($methodReflection)) {
            return null;
        }
        $spreadParameterReflections = $this->spreadVariablesCollector->resolveFromMethodReflection($methodReflection);
        if ($spreadParameterReflections === []) {
            return null;
        }
        \reset($spreadParameterReflections);
        $firstSpreadParamPosition = \key($spreadParameterReflections);
        $variadicArgs = $this->resolveVariadicArgsByVariadicParams($methodCall, $firstSpreadParamPosition);
        if ($this->hasUnpackedArgs($variadicArgs)) {
            $this->changeArgToPacked($variadicArgs, $methodCall);
            return $methodCall;
        }
        if ($variadicArgs !== []) {
            $array = $this->nodeFactory->createArray($variadicArgs);
            $spreadArg = $methodCall->args[$firstSpreadParamPosition] ?? null;
            // already set value
            if ($spreadArg instanceof \PhpParser\Node\Arg && $spreadArg->value instanceof \PhpParser\Node\Expr\Array_) {
                return null;
            }
            if (\count($variadicArgs) === 1) {
                return null;
            }
            $methodCall->args[$firstSpreadParamPosition] = new \PhpParser\Node\Arg($array);
            $this->removeLaterArguments($methodCall, $firstSpreadParamPosition);
            return $methodCall;
        }
        return null;
    }
    /**
     * @return Arg[]
     */
    private function resolveVariadicArgsByVariadicParams(\PhpParser\Node\Expr\MethodCall $methodCall, int $firstSpreadParamPosition) : array
    {
        $variadicArgs = [];
        foreach ($methodCall->args as $position => $arg) {
            if ($position < $firstSpreadParamPosition) {
                continue;
            }
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $variadicArgs[] = $arg;
        }
        return $variadicArgs;
    }
    private function removeLaterArguments(\PhpParser\Node\Expr\MethodCall $methodCall, int $argumentPosition) : void
    {
        $argCount = \count($methodCall->args);
        for ($i = $argumentPosition + 1; $i < $argCount; ++$i) {
            unset($methodCall->args[$i]);
        }
    }
    /**
     * @param Arg[] $variadicArgs
     */
    private function changeArgToPacked(array $variadicArgs, \PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        foreach ($variadicArgs as $position => $variadicArg) {
            if ($variadicArg->unpack) {
                $variadicArg->unpack = \false;
                $methodCall->args[$position] = $variadicArg;
            }
        }
    }
    /**
     * @param Arg[] $args
     */
    private function hasUnpackedArgs(array $args) : bool
    {
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
}
