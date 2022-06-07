<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector
     */
    private $spreadVariablesCollector;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Reflection\VendorLocationDetector
     */
    private $vendorLocationDetector;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    public function __construct(SpreadVariablesCollector $spreadVariablesCollector, ReflectionResolver $reflectionResolver, VendorLocationDetector $vendorLocationDetector, ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->spreadVariablesCollector = $spreadVariablesCollector;
        $this->reflectionResolver = $reflectionResolver;
        $this->vendorLocationDetector = $vendorLocationDetector;
        $this->classChildAnalyzer = $classChildAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove spread operator', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, MethodCall::class];
    }
    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        return $this->refactorMethodCall($node);
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->isInPHPUnitTestCase($classReflection, $classMethod)) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->classChildAnalyzer->hasParentClassMethod($classReflection, $methodName)) {
            return null;
        }
        $spreadParams = $this->spreadVariablesCollector->resolveFromClassMethod($classMethod);
        if ($spreadParams === []) {
            return null;
        }
        foreach ($spreadParams as $spreadParam) {
            $spreadParam->variadic = \false;
            $spreadParam->type = new Identifier('array');
            $spreadParam->default = $this->nodeFactory->createArray([]);
        }
        return $classMethod;
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?MethodCall
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        // skip those in vendor
        if ($this->vendorLocationDetector->detectMethodReflection($methodReflection)) {
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
            if ($spreadArg instanceof Arg && $spreadArg->value instanceof Array_) {
                return null;
            }
            if (\count($variadicArgs) === 1) {
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
    private function resolveVariadicArgsByVariadicParams(MethodCall $methodCall, int $firstSpreadParamPosition) : array
    {
        $variadicArgs = [];
        foreach ($methodCall->args as $position => $arg) {
            if ($position < $firstSpreadParamPosition) {
                continue;
            }
            if (!$arg instanceof Arg) {
                continue;
            }
            $variadicArgs[] = $arg;
        }
        return $variadicArgs;
    }
    private function removeLaterArguments(MethodCall $methodCall, int $argumentPosition) : void
    {
        $argCount = \count($methodCall->args);
        for ($i = $argumentPosition + 1; $i < $argCount; ++$i) {
            unset($methodCall->args[$i]);
        }
    }
    /**
     * @param Arg[] $variadicArgs
     */
    private function changeArgToPacked(array $variadicArgs, MethodCall $methodCall) : void
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
    private function isInPHPUnitTestCase(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        return $classReflection->isSubclassOf('PHPUnit\\Framework\\TestCase');
    }
}
