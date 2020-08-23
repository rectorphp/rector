<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertRegExpRector\AssertRegExpRectorTest
 */
final class AssertRegExpRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private const ASSERT_SAME = 'assertSame';

    /**
     * @var string
     */
    private const ASSERT_EQUALS = 'assertEquals';

    /**
     * @var string
     */
    private const ASSERT_NOT_SAME = 'assertNotSame';

    /**
     * @var string
     */
    private const ASSERT_NOT_EQUALS = 'assertNotEquals';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertSame(1, preg_match("/^Message for ".*"\.$/", $string), $message);',
                    '$this->assertRegExp("/^Message for ".*"\.$/", $string, $message);'
                ),
                new CodeSample(
                    '$this->assertEquals(false, preg_match("/^Message for ".*"\.$/", $string), $message);',
                    '$this->assertNotRegExp("/^Message for ".*"\.$/", $string, $message);'
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
        if (! $this->isPHPUnitMethodNames(
            $node,
            [self::ASSERT_SAME, self::ASSERT_EQUALS, self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS]
        )) {
            return null;
        }

        /** @var FuncCall|Node $secondArgumentValue */
        $secondArgumentValue = $node->args[1]->value;

        if (! $secondArgumentValue instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($secondArgumentValue, 'preg_match')) {
            return null;
        }

        $oldMethodName = $this->getName($node->name);
        if ($oldMethodName === null) {
            return null;
        }

        $oldFirstArgument = $node->args[0]->value;
        $oldCondition = $this->resolveOldCondition($oldFirstArgument);

        $this->renameMethod($node, $oldMethodName, $oldCondition);
        $this->moveFunctionArgumentsUp($node);

        return $node;
    }

    private function resolveOldCondition(Node $node): int
    {
        if ($node instanceof LNumber) {
            return $node->value;
        }

        if ($node instanceof ConstFetch) {
            return $this->isTrue($node) ? 1 : 0;
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function renameMethod(Node $node, string $oldMethodName, int $oldCondition): void
    {
        if (in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], true) && $oldCondition === 1
            || in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], true) && $oldCondition === 0
        ) {
            $node->name = new Identifier('assertRegExp');
        }

        if (in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], true) && $oldCondition === 0
            || in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], true) && $oldCondition === 1
        ) {
            $node->name = new Identifier('assertNotRegExp');
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function moveFunctionArgumentsUp(Node $node): void
    {
        $oldArguments = $node->args;

        /** @var FuncCall $pregMatchFunction */
        $pregMatchFunction = $oldArguments[1]->value;

        $regex = $pregMatchFunction->args[0];
        $variable = $pregMatchFunction->args[1];

        unset($oldArguments[0], $oldArguments[1]);

        $node->args = array_merge([$regex, $variable], $oldArguments);
    }
}
