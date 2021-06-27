<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
    public function __construct(\Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector $spreadVariablesCollector, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->spreadVariablesCollector = $spreadVariablesCollector;
        $this->reflectionResolver = $reflectionResolver;
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
        if ($this->skipForVendor($methodReflection)) {
            return null;
        }
        $spreadParameterReflections = $this->spreadVariablesCollector->resolveFromMethodReflection($methodReflection);
        if ($spreadParameterReflections === []) {
            return null;
        }
        \reset($spreadParameterReflections);
        $firstSpreadParamPosition = \key($spreadParameterReflections);
        $variadicArgs = $this->resolveVariadicArgsByVariadicParams($methodCall, $firstSpreadParamPosition);
        $hasUnpacked = \false;
        foreach ($variadicArgs as $position => $variadicArg) {
            if ($variadicArg->unpack) {
                $variadicArg->unpack = \false;
                $hasUnpacked = \true;
                $methodCall->args[$position] = $variadicArg;
            }
        }
        if ($hasUnpacked) {
            return $methodCall;
        }
        $methodCall->args[$firstSpreadParamPosition] = new \PhpParser\Node\Arg($this->nodeFactory->createArray($variadicArgs));
        return $methodCall;
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
            $variadicArgs[] = $arg;
            $this->nodeRemover->removeArg($methodCall, $position);
        }
        return $variadicArgs;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function skipForVendor($functionLikeReflection) : bool
    {
        if ($functionLikeReflection instanceof \PHPStan\Reflection\Php\PhpFunctionReflection) {
            $fileName = $functionLikeReflection->getFileName();
        } elseif ($functionLikeReflection instanceof \PHPStan\Reflection\MethodReflection) {
            $declaringClassReflection = $functionLikeReflection->getDeclaringClass();
            $fileName = $declaringClassReflection->getFileName();
        } else {
            return \false;
        }
        // probably internal
        if ($fileName === \false) {
            return \true;
        }
        return \strpos($fileName, '/vendor/') !== \false;
    }
}
