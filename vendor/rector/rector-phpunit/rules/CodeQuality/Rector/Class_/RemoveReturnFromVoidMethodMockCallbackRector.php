<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\Enum\ClassName;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\SetUpAssignedMockTypesResolver;
use Rector\PHPUnit\CodeQuality\Reflection\MethodParametersAndReturnTypesResolver;
use Rector\PHPUnit\CodeQuality\ValueObject\ParamTypesAndReturnType;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\RemoveReturnFromVoidMethodMockCallbackRector\RemoveReturnFromVoidMethodMockCallbackRectorTest
 */
final class RemoveReturnFromVoidMethodMockCallbackRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private SetUpAssignedMockTypesResolver $setUpAssignedMockTypesResolver;
    /**
     * @readonly
     */
    private MethodParametersAndReturnTypesResolver $methodParametersAndReturnTypesResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, SetUpAssignedMockTypesResolver $setUpAssignedMockTypesResolver, MethodParametersAndReturnTypesResolver $methodParametersAndReturnTypesResolver, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->setUpAssignedMockTypesResolver = $setUpAssignedMockTypesResolver;
        $this->methodParametersAndReturnTypesResolver = $methodParametersAndReturnTypesResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Drop the value return and type the closure as void, when a mocked callback stubs a void method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createMock(SomeClass::class)
            ->method('run')
            ->willReturnCallback(function ($arg) {
                echo $arg;

                return true;
            });
    }
}

final class SomeClass
{
    public function run($arg): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createMock(SomeClass::class)
            ->method('run')
            ->willReturnCallback(function ($arg): void {
                echo $arg;
            });
    }
}

final class SomeClass
{
    public function run($arg): void
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $currentClassReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$currentClassReflection instanceof ClassReflection) {
            return null;
        }
        $propertyNameToMockedTypes = $this->setUpAssignedMockTypesResolver->resolveFromClass($node);
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $subNode) use (&$hasChanged, $propertyNameToMockedTypes, $currentClassReflection) {
            if (!$subNode instanceof MethodCall || $subNode->isFirstClassCallable()) {
                return null;
            }
            $closure = $this->matchInnerClosure($subNode);
            if (!$closure instanceof Closure) {
                return null;
            }
            if (!$this->isMockedVoidMethodCall($subNode, $propertyNameToMockedTypes, $currentClassReflection)) {
                return null;
            }
            if ($this->refactorClosureToVoid($closure)) {
                $hasChanged = \true;
            }
            return null;
        });
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function matchInnerClosure(MethodCall $methodCall): ?Closure
    {
        if ($this->isName($methodCall->name, 'willReturnCallback')) {
            $innerArg = $methodCall->getArgs()[0];
            if ($innerArg->value instanceof Closure) {
                return $innerArg->value;
            }
            return null;
        }
        if ($this->isName($methodCall->name, 'with')) {
            $withFirstArg = $methodCall->getArgs()[0];
            if (!$withFirstArg->value instanceof MethodCall) {
                return null;
            }
            $nestedMethodCall = $withFirstArg->value;
            if (!$this->isName($nestedMethodCall->name, 'callback')) {
                return null;
            }
            $nestedArg = $nestedMethodCall->getArgs()[0];
            if ($nestedArg->value instanceof Closure) {
                return $nestedArg->value;
            }
        }
        return null;
    }
    /**
     * @param array<string, string> $propertyNameToMockedTypes
     */
    private function isMockedVoidMethodCall(MethodCall $methodCall, array $propertyNameToMockedTypes, ClassReflection $currentClassReflection): bool
    {
        if (!$methodCall->var instanceof MethodCall) {
            return \false;
        }
        $parentMethodCall = $methodCall->var;
        if (!$this->isName($parentMethodCall->name, 'method')) {
            return \false;
        }
        $methodNameExpr = $parentMethodCall->getArgs()[0]->value;
        if (!$methodNameExpr instanceof String_) {
            return \false;
        }
        $methodName = $methodNameExpr->value;
        $callerType = $this->getType($parentMethodCall->var);
        if ($callerType instanceof ObjectType && in_array($callerType->getClassName(), [PHPUnitClassName::INVOCATION_MOCKER, PHPUnitClassName::INVOCATION_STUBBER], \true)) {
            $parentMethodCall = $parentMethodCall->var;
            if ($parentMethodCall instanceof MethodCall) {
                $callerType = $this->getType($parentMethodCall->var);
            }
        }
        $callerType = $this->fallbackMockedObjectInSetUp($callerType, $parentMethodCall, $propertyNameToMockedTypes);
        if (!$callerType instanceof IntersectionType) {
            return \false;
        }
        $paramTypesAndReturnType = $this->methodParametersAndReturnTypesResolver->resolveFromReflection($callerType, $methodName, $currentClassReflection);
        if (!$paramTypesAndReturnType instanceof ParamTypesAndReturnType) {
            return \false;
        }
        return $paramTypesAndReturnType->getReturnType() instanceof VoidType;
    }
    private function refactorClosureToVoid(Closure $closure): bool
    {
        $nodeFinder = new NodeFinder();
        // nested function-likes have their own return scope, skip to stay safe
        $nestedFunctionLikes = $nodeFinder->find($closure->stmts, static fn(Node $node): bool => $node instanceof Closure || $node instanceof ArrowFunction || $node instanceof Function_);
        if ($nestedFunctionLikes !== []) {
            return \false;
        }
        /** @var Return_[] $returns */
        $returns = $nodeFinder->findInstanceOf($closure->stmts, Return_::class);
        $valueReturns = array_filter($returns, static fn(Return_ $return): bool => $return->expr instanceof Expr);
        // nothing to drop, typing void is left to TypeWillReturnCallableArrowFunctionRector
        if ($valueReturns === []) {
            return \false;
        }
        // only strip side-effect-free values, to not lose behavior
        foreach ($valueReturns as $valueReturn) {
            $returnedExpr = $valueReturn->expr;
            if (!$returnedExpr instanceof Expr) {
                continue;
            }
            if (!$this->isPureValue($returnedExpr)) {
                return \false;
            }
        }
        foreach ($valueReturns as $valueReturn) {
            $valueReturn->expr = null;
        }
        // drop a now-empty trailing "return;"
        $lastStmt = $closure->stmts[array_key_last($closure->stmts)] ?? null;
        if ($lastStmt instanceof Return_ && !$lastStmt->expr instanceof Expr) {
            array_pop($closure->stmts);
        }
        $closure->returnType = new Identifier('void');
        return \true;
    }
    private function isPureValue(Expr $expr): bool
    {
        return $expr instanceof Scalar || $expr instanceof ConstFetch || $expr instanceof Variable;
    }
    /**
     * @param array<string, string> $propertyNameToMockedTypes
     */
    private function fallbackMockedObjectInSetUp(Type $callerType, Expr $expr, array $propertyNameToMockedTypes): Type
    {
        if (!$callerType instanceof ObjectType && !$callerType instanceof NeverType) {
            return $callerType;
        }
        if (!$expr instanceof MethodCall) {
            return $callerType;
        }
        if ($callerType instanceof ObjectType && $callerType->getClassName() !== ClassName::MOCK_OBJECT) {
            return $callerType;
        }
        // type is missing, because of "final" keyword on mocked class
        // resolve from setUp assignment instead
        if (!$expr->var instanceof PropertyFetch && !$expr->var instanceof Variable) {
            return $callerType;
        }
        if ($expr->var instanceof Variable) {
            $propertyOrVariableName = $this->getName($expr->var);
        } else {
            $propertyOrVariableName = $this->getName($expr->var->name);
        }
        if (isset($propertyNameToMockedTypes[$propertyOrVariableName])) {
            $mockedType = $propertyNameToMockedTypes[$propertyOrVariableName];
            return new IntersectionType([$callerType, new ObjectType($mockedType)]);
        }
        return $callerType;
    }
}
