<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit110\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit110\Rector\ClassMethod\MockObjectArgCreateStubToCreateMockRector\MockObjectArgCreateStubToCreateMockRectorTest
 */
final class MockObjectArgCreateStubToCreateMockRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=11.0');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change createStub() to createMock(), when the created variable is passed to a method that requires MockObject type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMock = $this->createStub(SomeClass::class);
        $this->prepareMock($someMock);
    }

    private function prepareMock(MockObject $someMock): void
    {
        $someMock->expects($this->once())
            ->method('someMethod');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMock = $this->createMock(SomeClass::class);
        $this->prepareMock($someMock);
    }

    private function prepareMock(MockObject $someMock): void
    {
        $someMock->expects($this->once())
            ->method('someMethod');
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $createStubMethodCallsByVariableName = $this->collectCreateStubAssigns($node);
        if ($createStubMethodCallsByVariableName === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($createStubMethodCallsByVariableName as $variableName => $createStubMethodCall) {
            if (!$this->isPassedAsMockObjectArg($node, (string) $variableName)) {
                continue;
            }
            $createStubMethodCall->name = new Identifier('createMock');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return array<string, MethodCall>
     */
    private function collectCreateStubAssigns(ClassMethod $classMethod): array
    {
        $createStubMethodCallsByVariableName = [];
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $assign->expr;
            if (!$this->isName($methodCall->name, 'createStub')) {
                continue;
            }
            $variableName = $this->getName($assign->var);
            if ($variableName === null) {
                continue;
            }
            $createStubMethodCallsByVariableName[$variableName] = $methodCall;
        }
        return $createStubMethodCallsByVariableName;
    }
    private function isPassedAsMockObjectArg(ClassMethod $classMethod, string $variableName): bool
    {
        /** @var array<MethodCall|StaticCall> $callLikes */
        $callLikes = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, [MethodCall::class, StaticCall::class]);
        foreach ($callLikes as $callLike) {
            if ($callLike->isFirstClassCallable()) {
                continue;
            }
            foreach ($callLike->getArgs() as $argIndex => $arg) {
                if (!$arg->value instanceof Variable) {
                    continue;
                }
                if (!$this->isName($arg->value, $variableName)) {
                    continue;
                }
                if ($this->isMockObjectParam($callLike, $arg->name, $argIndex)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function isMockObjectParam($callLike, ?Identifier $identifier, int $argIndex): bool
    {
        $methodReflection = $callLike instanceof MethodCall ? $this->reflectionResolver->resolveMethodReflectionFromMethodCall($callLike) : $this->reflectionResolver->resolveMethodReflectionFromStaticCall($callLike);
        if (!$methodReflection instanceof MethodReflection) {
            return \false;
        }
        $mockObjectType = new ObjectType(PHPUnitClassName::MOCK_OBJECT);
        foreach ($methodReflection->getVariants() as $parametersAcceptor) {
            $parameters = $parametersAcceptor->getParameters();
            if ($identifier instanceof Identifier) {
                foreach ($parameters as $parameter) {
                    if ($parameter->getName() !== $identifier->toString()) {
                        continue;
                    }
                    if ($mockObjectType->isSuperTypeOf($parameter->getType())->yes()) {
                        return \true;
                    }
                }
                continue;
            }
            if (!isset($parameters[$argIndex])) {
                continue;
            }
            if ($mockObjectType->isSuperTypeOf($parameters[$argIndex]->getType())->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
