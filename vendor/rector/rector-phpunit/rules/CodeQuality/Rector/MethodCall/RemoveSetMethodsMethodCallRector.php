<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/pull/3687
 * @changelog https://github.com/sebastianbergmann/phpunit/commit/d618fa3fda437421264dcfa1413a474f306f79c4
 * @changelog https://stackoverflow.com/questions/65075204/method-setmethods-is-deprecated-when-try-to-write-a-php-unit-test
 *
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\RemoveSetMethodsMethodCallRector\RemoveSetMethodsMethodCallRectorTest
 */
final class RemoveSetMethodsMethodCallRector extends AbstractRector
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
        return new RuleDefinition('Remove "setMethods()" method as never used', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (!$this->isName($node->name, 'setMethods')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('PHPUnit\\Framework\\MockObject\\MockBuilder'))) {
            return null;
        }
        return $node->var;
    }
}
