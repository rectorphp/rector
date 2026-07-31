<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit110\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * The assertContainsOnly*() methods were added and assertContainsOnly() deprecated in PHPUnit 11.5
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/6055
 * @see https://github.com/sebastianbergmann/phpunit/blob/11.5.0/ChangeLog-11.5.md
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector\AssertContainsOnlyMethodCallRectorTest
 */
final class AssertContainsOnlyMethodCallRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const TYPE_VALUE_TO_METHOD = ['array' => 'assertContainsOnlyArray', 'bool' => 'assertContainsOnlyBool', 'boolean' => 'assertContainsOnlyBool', 'callable' => 'assertContainsOnlyCallable', 'double' => 'assertContainsOnlyFloat', 'float' => 'assertContainsOnlyFloat', 'int' => 'assertContainsOnlyInt', 'integer' => 'assertContainsOnlyInt', 'iterable' => 'assertContainsOnlyIterable', 'null' => 'assertContainsOnlyNull', 'numeric' => 'assertContainsOnlyNumeric', 'object' => 'assertContainsOnlyObject', 'real' => 'assertContainsOnlyFloat', 'resource' => 'assertContainsOnlyResource', 'resource (closed)' => 'assertContainsOnlyClosedResource', 'scalar' => 'assertContainsOnlyScalar', 'string' => 'assertContainsOnlyString'];
    public function __construct(ValueResolver $valueResolver, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=11.5');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces `Assert::assertContainsOnly()` calls with type-specific `Assert::assertContainsOnly*()` calls', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMethod(): void
    {
        $this->assertContainsOnly('string', ['a', 'b']);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function testMethod(): void
    {
        $this->assertContainsOnlyString(['a', 'b']);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitTestCaseCall($node) || !$this->isName($node->name, 'assertContainsOnly')) {
            return null;
        }
        $typeArg = $node->getArg('type', 0);
        $haystackArg = $node->getArg('haystack', 1);
        if (!$typeArg instanceof Arg || !$haystackArg instanceof Arg) {
            return null;
        }
        $typeValue = $this->valueResolver->getValue($typeArg);
        if (!is_string($typeValue)) {
            return null;
        }
        $newMethodName = self::TYPE_VALUE_TO_METHOD[$typeValue] ?? null;
        if ($newMethodName === null) {
            return null;
        }
        // the $isNativeType argument can turn the type into a class name, that has no type-specific method
        $isNativeTypeArg = $node->getArg('isNativeType', 2);
        if ($isNativeTypeArg instanceof Arg && !$this->valueResolver->isTrue($isNativeTypeArg->value) && !$this->valueResolver->isNull($isNativeTypeArg->value)) {
            return null;
        }
        $newArgs = [new Arg($haystackArg->value)];
        $messageArg = $node->getArg('message', 3);
        if ($messageArg instanceof Arg) {
            $newArgs[] = new Arg($messageArg->value);
        }
        if ($node instanceof MethodCall) {
            return new MethodCall($node->var, $newMethodName, $newArgs);
        }
        return new StaticCall($node->class, $newMethodName, $newArgs);
    }
}
