<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\AssertCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/90e9e0379584bdf34220322e202617cd56d8ba65
 * @see https://github.com/sebastianbergmann/phpunit/commit/a4b60a5c625ff98a52bb3222301d223be7367483
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\AssertEqualsParameterToSpecificMethodsTypeRectorTest
 */
final class AssertEqualsParameterToSpecificMethodsTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\AssertCallFactory
     */
    private $assertCallFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeFactory\AssertCallFactory $assertCallFactory, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->assertCallFactory = $assertCallFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change assertEquals()/assertNotEquals() method parameters to new specific alternatives', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEquals('string', $value, 'message', 5.0);

        $this->assertEquals('string', $value, 'message', 0.0, 20);

        $this->assertEquals('string', $value, 'message', 0.0, 10, true);

        $this->assertEquals('string', $value, 'message', 0.0, 10, false, true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $value = 'value';
        $this->assertEqualsWithDelta('string', $value, 5.0, 'message');

        $this->assertEquals('string', $value, 'message', 0.0);

        $this->assertEqualsCanonicalizing('string', $value, 'message');

        $this->assertEqualsIgnoringCase('string', $value, 'message');
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertEquals', 'assertNotEquals'])) {
            return null;
        }
        // 1. refactor to "assertEqualsIgnoringCase()"
        $newMethodCall = $this->processAssertEqualsIgnoringCase($node);
        if ($newMethodCall !== null) {
            return $newMethodCall;
        }
        // 2. refactor to "assertEqualsCanonicalizing()"
        $newMethodCall = $this->processAssertEqualsCanonicalizing($node);
        if ($newMethodCall !== null) {
            return $newMethodCall;
        }
        if (isset($node->args[4])) {
            // add new node only in case of non-default value
            unset($node->args[4]);
        }
        return $this->processAssertEqualsWithDelta($node);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     * @return \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|null
     */
    private function processAssertEqualsIgnoringCase($call)
    {
        $args = $call->getArgs();
        if (!isset($args[6])) {
            return null;
        }
        unset($call->args[6]);
        if ($this->valueResolver->isFalse($args[6]->value)) {
            return $call;
        }
        $newMethodCall = $this->assertCallFactory->createCallWithName($call, 'assertEqualsIgnoringCase');
        $newMethodCall->args[0] = $call->args[0];
        $newMethodCall->args[1] = $call->args[1];
        if (!$this->valueResolver->isValue($args[2]->value, '')) {
            $newMethodCall->args[2] = $args[2];
        }
        return $newMethodCall;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function processAssertEqualsCanonicalizing($call)
    {
        $args = $call->getArgs();
        if (!isset($args[5])) {
            return null;
        }
        // add new node only in case of non-default value
        unset($call->args[5]);
        if (!$this->valueResolver->isTrue($args[5]->value)) {
            return $call;
        }
        $newMethodCall = $this->assertCallFactory->createCallWithName($call, 'assertEqualsCanonicalizing');
        $newMethodCall->args[0] = $args[0];
        $newMethodCall->args[1] = $args[1];
        // keep only non empty message
        if (!$this->valueResolver->isValue($args[2]->value, '')) {
            $newMethodCall->args[2] = $args[2];
        }
        return $newMethodCall;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function processAssertEqualsWithDelta($call)
    {
        $args = $call->getArgs();
        if (!isset($args[3])) {
            return null;
        }
        // add new node only in case of non-default value
        $thirdArg = $call->getArgs()[3];
        unset($call->args[3]);
        if ($this->valueResolver->isValue($thirdArg->value, 0.0)) {
            return null;
        }
        $newMethodCall = $this->assertCallFactory->createCallWithName($call, 'assertEqualsWithDelta');
        $newMethodCall->args[0] = $call->args[0];
        $newMethodCall->args[1] = $call->args[1];
        $newMethodCall->args[2] = $thirdArg;
        $secondArg = $args[2];
        // keep only non empty message
        if (!$this->valueResolver->isValue($secondArg->value, '')) {
            $newMethodCall->args[3] = $secondArg;
        }
        return $newMethodCall;
    }
}
