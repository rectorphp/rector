<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/6053
 * @see https://github.com/sebastianbergmann/phpunit/blob/12.0.0/ChangeLog-12.0.md
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\MethodCall\AssertIsTypeMethodCallRector\AssertIsTypeMethodCallRectorTest
 */
final class AssertIsTypeMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    private const IS_TYPE_VALUE_TO_METHOD = ['array' => 'isArray', 'bool' => 'isBool', 'boolean' => 'isBool', 'callable' => 'isCallable', 'double' => 'isFloat', 'float' => 'isFloat', 'integer' => 'isInt', 'int' => 'isInt', 'iterable' => 'isIterable', 'null' => 'isNull', 'numeric' => 'isNumeric', 'object' => 'isObject', 'real' => 'isFloat', 'resource' => 'isResource', 'resource (closed)' => 'isClosedResource', 'scalar' => 'isScalar', 'string' => 'isString'];
    public function __construct(ValueResolver $valueResolver, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces `Assert::isType()` calls with type-specific `Assert::is*()` calls', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMethod(): void
    {
        $this->assertThat([], $this->isType('array'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMethod(): void
    {
        $this->assertThat([], $this->isArray());
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?\PhpParser\Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isPHPUnitTestCaseCall($node) || !$this->isName($node->name, 'isType')) {
            return null;
        }
        if (count($node->getArgs()) !== 1) {
            return null;
        }
        $arg = $node->getArg('type', 0);
        if (!$arg instanceof Arg) {
            return null;
        }
        $argValue = $this->valueResolver->getValue($arg);
        if (isset(self::IS_TYPE_VALUE_TO_METHOD[$argValue])) {
            if ($node instanceof MethodCall) {
                return new MethodCall($node->var, self::IS_TYPE_VALUE_TO_METHOD[$argValue]);
            }
            return new StaticCall($node->class, self::IS_TYPE_VALUE_TO_METHOD[$argValue]);
        }
        return null;
    }
}
