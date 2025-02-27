<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector\AssertEmptyNullableObjectToAssertInstanceofRectorTest
 */
final class AssertEmptyNullableObjectToAssertInstanceofRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertNotEmpty() and assertNotNull() on an object to more clear assertInstanceof()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $someObject = new stdClass();

        $this->assertNotEmpty($someObject);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $someObject = new stdClass();

        $this->assertInstanceof(stdClass::class, $someObject);
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
        if (!$this->isNames($node->name, ['assertNotEmpty', 'assertEmpty', 'assertNull', 'assertNotNull'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $firstArgType = $this->getType($firstArg->value);
        if (!$firstArgType instanceof UnionType) {
            return null;
        }
        $pureType = TypeCombinator::removeNull($firstArgType);
        if (!$pureType instanceof ObjectType) {
            return null;
        }
        $methodName = $this->isNames($node->name, ['assertEmpty', 'assertNull']) ? 'assertNotInstanceOf' : 'assertInstanceOf';
        $node->name = new Identifier($methodName);
        $fullyQualified = new FullyQualified($pureType->getClassName());
        $customMessageArg = $node->getArgs()[1] ?? null;
        $node->args[0] = new Arg(new ClassConstFetch($fullyQualified, 'class'));
        $node->args[1] = $firstArg;
        if ($customMessageArg instanceof Arg) {
            $node->args[] = $customMessageArg;
        }
        return $node;
    }
}
