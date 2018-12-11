<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/90e9e0379584bdf34220322e202617cd56d8ba65
 * @see https://github.com/sebastianbergmann/phpunit/commit/a4b60a5c625ff98a52bb3222301d223be7367483
 */
final class AssertEqualsParameterToSpecificMethodsTypeRector extends AbstractPHPUnitRector
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    public function __construct(ConstExprEvaluator $constExprEvaluator)
    {
        $this->constExprEvaluator = $constExprEvaluator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertEquals()/assertNotEquals() method parameters to new specific alternatives',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return string[]
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['assertEquals', 'assertNotEquals'])) {
            return null;
        }

        // 1. refactor to "assertEqualsIgnoringCase()"
        $this->processAssertEqualsIgnoringCase($node);

        // 2. refactor to "assertEqualsCanonicalizing()"
        $this->processAssertEqualsCanonicalizing($node);

        // 3. remove $maxDepth
        if (isset($node->args[4])) {
            // add new node only in case of non-default value
            unset($node->args[4]);
        }

        // 4. refactor $delta to "assertEqualsWithDelta()"
        $this->processAssertEqualsWithDelta($node);

        $node->args = array_values($node->args);

        return $node;
    }

    /**
     * @return mixed
     */
    public function getValue(Expr $node)
    {
        return $this->constExprEvaluator->evaluateSilently($node);
    }

    private function processAssertEqualsIgnoringCase(MethodCall $methodCallNode): void
    {
        if (isset($methodCallNode->args[6])) {
            if ($this->isTrue($methodCallNode->args[6]->value)) {
                $methodCall = new MethodCall($methodCallNode->var, 'assertEqualsIgnoringCase');
                $methodCall->args[0] = $methodCallNode->args[0];
                $methodCall->args[1] = $methodCallNode->args[1];
                $methodCall->args[2] = $methodCallNode->args[2];
                $this->addNodeAfterNode($methodCall, $methodCallNode);
            }

            unset($methodCallNode->args[6]);
        }
    }

    private function processAssertEqualsCanonicalizing(MethodCall $methodCallNode): void
    {
        if (isset($methodCallNode->args[5])) {
            // add new node only in case of non-default value
            if ($this->isTrue($methodCallNode->args[5]->value)) {
                $methodCall = new MethodCall($methodCallNode->var, 'assertEqualsCanonicalizing');
                $methodCall->args[0] = $methodCallNode->args[0];
                $methodCall->args[1] = $methodCallNode->args[1];
                $methodCall->args[2] = $methodCallNode->args[2];
                $this->addNodeAfterNode($methodCall, $methodCallNode);
            }

            unset($methodCallNode->args[5]);
        }
    }

    private function processAssertEqualsWithDelta(MethodCall $methodCallNode): void
    {
        if (isset($methodCallNode->args[3])) {
            // add new node only in case of non-default value
            if ($this->getValue($methodCallNode->args[3]->value) !== 0.0) {
                $methodCall = new MethodCall($methodCallNode->var, 'assertEqualsWithDelta');
                $methodCall->args[0] = $methodCallNode->args[0];
                $methodCall->args[1] = $methodCallNode->args[1];
                $methodCall->args[2] = $methodCallNode->args[3];
                $methodCall->args[3] = $methodCallNode->args[2];
                $this->addNodeAfterNode($methodCall, $methodCallNode);
            }

            unset($methodCallNode->args[3]);
        }
    }
}
