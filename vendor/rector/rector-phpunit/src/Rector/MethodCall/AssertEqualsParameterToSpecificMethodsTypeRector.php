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
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/90e9e0379584bdf34220322e202617cd56d8ba65
 * @see https://github.com/sebastianbergmann/phpunit/commit/a4b60a5c625ff98a52bb3222301d223be7367483
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
        $this->processAssertEqualsIgnoringCase($node);
        // 2. refactor to "assertEqualsCanonicalizing()"
        $this->processAssertEqualsCanonicalizing($node);
        if (isset($node->args[4])) {
            // add new node only in case of non-default value
            unset($node->args[4]);
        }
        $this->processAssertEqualsWithDelta($node);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processAssertEqualsIgnoringCase($node) : void
    {
        if (isset($node->args[6])) {
            if ($this->valueResolver->isTrue($node->args[6]->value)) {
                $newMethodCall = $this->assertCallFactory->createCallWithName($node, 'assertEqualsIgnoringCase');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[2];
                $this->nodesToAddCollector->addNodeAfterNode($newMethodCall, $node);
            }
            unset($node->args[6]);
        }
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processAssertEqualsCanonicalizing($node) : void
    {
        if (isset($node->args[5])) {
            // add new node only in case of non-default value
            if ($this->valueResolver->isTrue($node->args[5]->value)) {
                $newMethodCall = $this->assertCallFactory->createCallWithName($node, 'assertEqualsCanonicalizing');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[2];
                $this->nodesToAddCollector->addNodeAfterNode($newMethodCall, $node);
            }
            unset($node->args[5]);
        }
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processAssertEqualsWithDelta($node) : void
    {
        if (isset($node->args[3])) {
            // add new node only in case of non-default value
            if (!$this->valueResolver->isValue($node->args[3]->value, 0.0)) {
                $newMethodCall = $this->assertCallFactory->createCallWithName($node, 'assertEqualsWithDelta');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[3];
                $newMethodCall->args[3] = $node->args[2];
                $this->nodesToAddCollector->addNodeAfterNode($newMethodCall, $node);
            }
            unset($node->args[3]);
        }
    }
}
