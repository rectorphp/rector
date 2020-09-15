<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see https://github.com/sebastianbergmann/phpunit/commit/90e9e0379584bdf34220322e202617cd56d8ba65
 * @see https://github.com/sebastianbergmann/phpunit/commit/a4b60a5c625ff98a52bb3222301d223be7367483
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector\AssertEqualsParameterToSpecificMethodsTypeRectorTest
 */
final class AssertEqualsParameterToSpecificMethodsTypeRector extends AbstractPHPUnitRector
{
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
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, ['assertEquals', 'assertNotEquals'])) {
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

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processAssertEqualsIgnoringCase(Node $node): void
    {
        if (isset($node->args[6])) {
            if ($this->isTrue($node->args[6]->value)) {
                $newMethodCall = $this->createPHPUnitCallWithName($node, 'assertEqualsIgnoringCase');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[2];
                $this->addNodeAfterNode($newMethodCall, $node);
            }

            unset($node->args[6]);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processAssertEqualsCanonicalizing(Node $node): void
    {
        if (isset($node->args[5])) {
            // add new node only in case of non-default value
            if ($this->isTrue($node->args[5]->value)) {
                $newMethodCall = $this->createPHPUnitCallWithName($node, 'assertEqualsCanonicalizing');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[2];
                $this->addNodeAfterNode($newMethodCall, $node);
            }

            unset($node->args[5]);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processAssertEqualsWithDelta(Node $node): void
    {
        if (isset($node->args[3])) {
            // add new node only in case of non-default value
            if ($this->getValue($node->args[3]->value) !== 0.0) {
                $newMethodCall = $this->createPHPUnitCallWithName($node, 'assertEqualsWithDelta');
                $newMethodCall->args[0] = $node->args[0];
                $newMethodCall->args[1] = $node->args[1];
                $newMethodCall->args[2] = $node->args[3];
                $newMethodCall->args[3] = $node->args[2];
                $this->addNodeAfterNode($newMethodCall, $node);
            }

            unset($node->args[3]);
        }
    }
}
