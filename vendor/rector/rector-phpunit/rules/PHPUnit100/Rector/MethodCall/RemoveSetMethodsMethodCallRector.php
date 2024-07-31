<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/pull/3687
 * @changelog https://github.com/sebastianbergmann/phpunit/commit/d618fa3fda437421264dcfa1413a474f306f79c4
 * @changelog https://stackoverflow.com/questions/65075204/method-setmethods-is-deprecated-when-try-to-write-a-php-unit-test
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\MethodCall\RemoveSetMethodsMethodCallRector\RemoveSetMethodsMethodCallRectorTest
 */
final class RemoveSetMethodsMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "setMethods()" method as never used, move methods to "addMethods()" if non-existent or @method magic', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMock = $this->getMockBuilder(SomeClass::class)
            ->setMethods(['run'])
            ->getMock();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $someMock = $this->getMockBuilder(SomeClass::class)
            ->getMock();
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'setMethods')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('PHPUnit\\Framework\\MockObject\\MockBuilder'))) {
            return null;
        }
        $mockedMagicMethodNames = $this->resolvedMockedMagicMethodNames($node);
        // rename to "addMethods()" to add magic method names
        if ($mockedMagicMethodNames !== []) {
            $node->name = new Identifier('addMethods');
            $node->args = $this->nodeFactory->createArgs([$mockedMagicMethodNames]);
            return $node;
        }
        return $node->var;
    }
    /**
     * Method names from @method, must remain mocked using "addMethods()" call
     *
     * @return string[]
     */
    private function resolvedMockedMagicMethodNames(MethodCall $setMethodsMethodCall) : array
    {
        $mockedClassName = $this->resolveMockedClassName($setMethodsMethodCall);
        // unable to resolve mocked class
        if (!\is_string($mockedClassName)) {
            return [];
        }
        $magicMethodNames = $this->resolveClassMagicMethodNames($mockedClassName);
        if ($magicMethodNames === []) {
            // no magic methods? nothing to keep
            return [];
        }
        $mockedMethodNames = $this->resolveSetMethodNames($setMethodsMethodCall);
        $magicSetMethodNames = \array_intersect($mockedMethodNames, $magicMethodNames);
        return \array_values($magicSetMethodNames);
    }
    /**
     * @return string[]
     */
    private function resolveClassMagicMethodNames(string $className) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return \array_keys($classReflection->getMethodTags());
    }
    /**
     * @return string[]
     */
    private function resolveSetMethodNames(MethodCall $setMethodsMethodCall) : array
    {
        if ($setMethodsMethodCall->isFirstClassCallable()) {
            return [];
        }
        $firstArg = $setMethodsMethodCall->getArgs()[0];
        $value = $this->valueResolver->getValue($firstArg->value);
        if (!\is_array($value)) {
            return [];
        }
        return $value;
    }
    /**
     * @return mixed
     */
    private function resolveMockedClassName(MethodCall $setMethodsMethodCall)
    {
        $parentMethodCall = $setMethodsMethodCall->var;
        while ($parentMethodCall instanceof MethodCall) {
            if ($this->isName($parentMethodCall->name, 'getMockBuilder')) {
                if ($parentMethodCall->isFirstClassCallable()) {
                    return null;
                }
                // resolve mocked class name
                $firstArg = $parentMethodCall->getArgs()[0];
                return $this->valueResolver->getValue($firstArg->value);
            }
            $parentMethodCall = $parentMethodCall->var;
        }
        return null;
    }
}
