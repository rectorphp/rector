<?php

declare (strict_types=1);
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
final class AssertCompareToSpecificMethodRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->functionNamesWithAssertMethods = [new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('count', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('sizeof', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('iterator_count', self::ASSERT_COUNT, self::ASSERT_NOT_COUNT), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('gettype', 'assertInternalType', 'assertNotInternalType'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('get_class', 'assertInstanceOf', 'assertNotInstanceOf')];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns vague php-only method in PHPUnit TestCase to more specific', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertSame(10, count($anything), "message");', '$this->assertCount(10, $anything, "message");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertNotEquals(get_class($value), stdClass::class);', '$this->assertNotInstanceOf(stdClass::class, $value);')]);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals'])) {
            return null;
        }
        // we need 2 args
        if (!isset($node->args[1])) {
            return null;
        }
        $firstArgument = $node->args[0];
        $secondArgument = $node->args[1];
        $firstArgumentValue = $firstArgument->value;
        $secondArgumentValue = $secondArgument->value;
        if ($secondArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processFuncCallArgumentValue($node, $secondArgumentValue, $firstArgument);
        }
        if ($firstArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processFuncCallArgumentValue($node, $firstArgumentValue, $secondArgument);
        }
        return null;
    }
    /**
     * @return MethodCall|StaticCall|null
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processFuncCallArgumentValue($node, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Arg $requiredArg) : ?\PhpParser\Node
    {
        foreach ($this->functionNamesWithAssertMethods as $functionNameWithAssertMethod) {
            if (!$this->isName($funcCall, $functionNameWithAssertMethod->getFunctionName())) {
                continue;
            }
            $this->renameMethod($node, $functionNameWithAssertMethod);
            $this->moveFunctionArgumentsUp($node, $funcCall, $requiredArg);
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function renameMethod($node, \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods $functionNameWithAssertMethods) : void
    {
        if ($this->isNames($node->name, ['assertSame', 'assertEquals'])) {
            $node->name = new \PhpParser\Node\Identifier($functionNameWithAssertMethods->getAssetMethodName());
        } elseif ($this->isNames($node->name, ['assertNotSame', 'assertNotEquals'])) {
            $node->name = new \PhpParser\Node\Identifier($functionNameWithAssertMethods->getNotAssertMethodName());
        }
    }
    /**
     * Handles custom error messages to not be overwrite by function with multiple args.
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function moveFunctionArgumentsUp($node, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Arg $requiredArg) : void
    {
        $node->args[1] = $funcCall->args[0];
        $node->args[0] = $requiredArg;
    }
}
