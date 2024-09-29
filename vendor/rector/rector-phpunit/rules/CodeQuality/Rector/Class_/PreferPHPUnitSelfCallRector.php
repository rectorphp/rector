<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\PreferPHPUnitSelfCallRector\PreferPHPUnitSelfCallRectorTest
 */
final class PreferPHPUnitSelfCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes PHPUnit calls from $this->assert*() to self::assert*()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        $this->assertEquals('expected', $result);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        self::assertEquals('expected', $result);
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged) : ?StaticCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $methodName = $this->getName($node->name);
            if (!\is_string($methodName)) {
                return null;
            }
            if (\strncmp($methodName, 'assert', \strlen('assert')) !== 0) {
                return null;
            }
            if (!$this->isName($node->var, 'this')) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('PHPUnit\\Framework\\TestCase'))) {
                return null;
            }
            $classReflection = $this->reflectionResolver->resolveClassReflection($node);
            if ($classReflection instanceof ClassReflection && $classReflection->hasNativeMethod($methodName)) {
                $method = $classReflection->getNativeMethod($methodName);
                if ($node->isFirstClassCallable()) {
                    return null;
                }
                if ($method->isStatic()) {
                    $hasChanged = \true;
                    return $this->nodeFactory->createStaticCall('self', $methodName, $node->getArgs());
                }
            }
            return null;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
