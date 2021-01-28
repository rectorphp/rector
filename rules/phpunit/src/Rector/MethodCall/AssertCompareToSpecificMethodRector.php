<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector\AssertCompareToSpecificMethodRectorTest
 */
final class AssertCompareToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_COUNT = 'assertCount';

    /**
     * @var string
     */
    private const ASSERT_NOT_COUNT = 'assertNotCount';

    /**
     * @var FunctionNameWithAssertMethods[]
     */
    private $functionNamesWithAssertMethods = [];

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->functionNamesWithAssertMethods = [
            new FunctionNameWithAssertMethods('count', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT),
            new FunctionNameWithAssertMethods('sizeof', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT),
            new FunctionNameWithAssertMethods('iterator_count', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT),
            new FunctionNameWithAssertMethods('gettype', 'assertInternalType', 'assertNotInternalType'),
            new FunctionNameWithAssertMethods('get_class', 'assertInstanceOf', 'assertNotInstanceOf'),
        ];
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns vague php-only method in PHPUnit TestCase to more specific',
            [
                new CodeSample(
                    '$this->assertSame(10, count($anything), "message");',
                    '$this->assertCount(10, $anything, "message");'
                ),
                new CodeSample(
                    '$this->assertNotEquals(get_class($value), stdClass::class);',
                    '$this->assertNotInstanceOf(stdClass::class, $value);'
                ),

            ]);
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
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodNames(
            $node,
            ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals']
        )) {
            return null;
        }

        // we need 2 args
        if (! isset($node->args[1])) {
            return null;
        }

        $firstArgument = $node->args[0];
        $secondArgument = $node->args[1];
        $firstArgumentValue = $firstArgument->value;
        $secondArgumentValue = $secondArgument->value;

        if ($secondArgumentValue instanceof FuncCall) {
            return $this->processFuncCallArgumentValue($node, $secondArgumentValue, $firstArgument);
        }

        if ($firstArgumentValue instanceof FuncCall) {
            return $this->processFuncCallArgumentValue($node, $firstArgumentValue, $secondArgument);
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @return MethodCall|StaticCall|null
     */
    private function processFuncCallArgumentValue(Node $node, FuncCall $funcCall, Arg $requiredArg): ?Node
    {
        foreach ($this->functionNamesWithAssertMethods as $functionNameWithAssertMethods) {
            if (! $this->isName($funcCall, $functionNameWithAssertMethods->getFunctionName())) {
                continue;
            }

            $this->renameMethod($node, $functionNameWithAssertMethods);
            $this->moveFunctionArgumentsUp($node, $funcCall, $requiredArg);

            return $node;
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function renameMethod(Node $node, FunctionNameWithAssertMethods $functionNameWithAssertMethods): void
    {
        if ($this->isNames($node->name, ['assertSame', 'assertEquals'])) {
            $node->name = new Identifier($functionNameWithAssertMethods->getAssetMethodName());
        } elseif ($this->isNames($node->name, ['assertNotSame', 'assertNotEquals'])) {
            $node->name = new Identifier($functionNameWithAssertMethods->getNotAssertMethodName());
        }
    }

    /**
     * Handles custom error messages to not be overwrite by function with multiple args.
     * @param StaticCall|MethodCall $node
     */
    private function moveFunctionArgumentsUp(Node $node, FuncCall $funcCall, Arg $requiredArg): void
    {
        $node->args[1] = $funcCall->args[0];
        $node->args[0] = $requiredArg;
    }
}
