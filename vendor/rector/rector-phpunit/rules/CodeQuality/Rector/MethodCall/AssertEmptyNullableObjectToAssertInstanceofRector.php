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
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector\AssertEmptyNullableObjectToAssertInstanceofRectorTest
 */
final class AssertEmptyNullableObjectToAssertInstanceofRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertNotEmpty() on an object to more clear assertInstanceof()', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (!$this->isNames($node->name, ['assertNotEmpty', 'assertEmpty'])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgType = $this->getType($firstArg->value);
        if (!$firstArgType instanceof UnionType) {
            return null;
        }
        $pureType = TypeCombinator::removeNull($firstArgType);
        if (!$pureType instanceof ObjectType) {
            return null;
        }
        $methodName = $this->isName($node->name, 'assertEmpty') ? 'assertNotInstanceOf' : 'assertInstanceOf';
        $node->name = new Identifier($methodName);
        $fullyQualified = new FullyQualified($pureType->getClassName());
        $node->args[0] = new Arg(new ClassConstFetch($fullyQualified, 'class'));
        $node->args[1] = $firstArg;
        return $node;
    }
}
