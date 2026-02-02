<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertThatToDirectAssertRector\AssertThatToDirectAssertRectorTest
 */
final class AssertThatToDirectAssertRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var array<string, string>
     */
    private const IS_TO_ASSERT_METHOD_MAP = ['isTrue' => 'assertTrue', 'isFalse' => 'assertFalse', 'isNull' => 'assertNull', 'isEmpty' => 'assertEmpty', 'isCountable' => 'assertIsCountable', 'isArray' => 'assertIsArray', 'isString' => 'assertIsString', 'isInt' => 'assertIsInt', 'isFloat' => 'assertIsFloat', 'isBool' => 'assertIsBool'];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $this->assertThat($value, $this->*()) to direct $this->assert*() method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertThat($value, $this->isTrue());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->assertTrue($value);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertThat'])) {
            return null;
        }
        $secondArg = $node->getArgs()[1];
        if (!$secondArg->value instanceof MethodCall) {
            return null;
        }
        $exactAssertMethodCall = $secondArg->value;
        $exactAssertName = $this->getName($exactAssertMethodCall->name);
        if ($exactAssertName === 'equalTo') {
            $node->name = new Identifier('assertEquals');
            $node->args[1] = $node->args[0];
            $node->args[0] = $exactAssertMethodCall->args[0];
            return $node;
        }
        foreach (self::IS_TO_ASSERT_METHOD_MAP as $isName => $assertName) {
            if (!$this->isName($exactAssertMethodCall->name, $isName)) {
                continue;
            }
            $node->name = new Identifier($assertName);
            unset($node->args[1]);
            return $node;
        }
        if ($this->isName($exactAssertMethodCall->name, 'isType')) {
            $exactFirstArg = $exactAssertMethodCall->getArgs()[0];
            $expectedType = $this->valueResolver->getValue($exactFirstArg->value);
            if (!is_string($expectedType)) {
                return null;
            }
            $node->name = new Identifier('assertIs' . ucfirst($expectedType));
            unset($node->args[1]);
            return $node;
        }
        return null;
    }
}
