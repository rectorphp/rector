<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Empty_;
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
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\AssertTrueFalseToSpecificMethodRectorTest
 */
final class AssertTrueFalseToSpecificMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var FunctionNameWithAssertMethods[]
     */
    private $functionNameWithAssertMethods = [];
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->functionNameWithAssertMethods = [new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_readable', 'assertIsReadable', 'assertNotIsReadable'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('array_key_exists', 'assertArrayHasKey', 'assertArrayNotHasKey'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('array_search', 'assertContains', 'assertNotContains'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('in_array', 'assertContains', 'assertNotContains'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('empty', 'assertEmpty', 'assertNotEmpty'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('file_exists', 'assertFileExists', 'assertFileNotExists'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_dir', 'assertDirectoryExists', 'assertDirectoryNotExists'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_infinite', 'assertInfinite', 'assertFinite'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_null', 'assertNull', 'assertNotNull'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_writable', 'assertIsWritable', 'assertNotIsWritable'), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_nan', 'assertNan', ''), new \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods('is_a', 'assertInstanceOf', 'assertNotInstanceOf')];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertTrue(is_readable($readmeFile), "message");', '$this->assertIsReadable($readmeFile, "message");')]);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertTrue', 'assertFalse', 'assertNotTrue', 'assertNotFalse'])) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Expr\FuncCall && !$firstArgumentValue instanceof \PhpParser\Node\Expr\Empty_) {
            return null;
        }
        foreach ($this->functionNameWithAssertMethods as $functionNameWithAssertMethod) {
            if (!$this->isName($firstArgumentValue, $functionNameWithAssertMethod->getFunctionName())) {
                continue;
            }
            $name = $this->getName($firstArgumentValue);
            if ($name === null) {
                return null;
            }
            $this->renameMethod($node, $functionNameWithAssertMethod);
            $this->moveFunctionArgumentsUp($node);
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function renameMethod($node, \Rector\PHPUnit\ValueObject\FunctionNameWithAssertMethods $functionNameWithAssertMethods) : void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;
        $oldMethodName = $identifierNode->toString();
        if (\in_array($oldMethodName, ['assertTrue', 'assertNotFalse'], \true)) {
            $node->name = new \PhpParser\Node\Identifier($functionNameWithAssertMethods->getAssetMethodName());
        }
        if ($functionNameWithAssertMethods->getNotAssertMethodName() === '') {
            return;
        }
        if (!\in_array($oldMethodName, ['assertFalse', 'assertNotTrue'], \true)) {
            return;
        }
        $node->name = new \PhpParser\Node\Identifier($functionNameWithAssertMethods->getNotAssertMethodName());
    }
    /**
     * Before:
     * - $this->assertTrue(array_key_exists('...', ['...']), 'second argument');
     *
     * After:
     * - $this->assertArrayHasKey('...', ['...'], 'second argument');
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function moveFunctionArgumentsUp($node) : void
    {
        $funcCallOrEmptyNode = $node->args[0]->value;
        if ($funcCallOrEmptyNode instanceof \PhpParser\Node\Expr\FuncCall) {
            $funcCallOrEmptyNodeName = $this->getName($funcCallOrEmptyNode);
            if ($funcCallOrEmptyNodeName === null) {
                return;
            }
            $funcCallOrEmptyNodeArgs = $funcCallOrEmptyNode->args;
            $oldArguments = $node->args;
            unset($oldArguments[0]);
            $node->args = $this->buildNewArguments($funcCallOrEmptyNodeName, $funcCallOrEmptyNodeArgs, $oldArguments);
        }
        if ($funcCallOrEmptyNode instanceof \PhpParser\Node\Expr\Empty_) {
            $node->args[0] = new \PhpParser\Node\Arg($funcCallOrEmptyNode->expr);
        }
    }
    /**
     * @param Arg[] $funcCallOrEmptyNodeArgs
     * @param Arg[] $oldArguments
     * @return mixed[]
     */
    private function buildNewArguments(string $funcCallOrEmptyNodeName, array $funcCallOrEmptyNodeArgs, array $oldArguments) : array
    {
        if (\in_array($funcCallOrEmptyNodeName, ['in_array', 'array_search'], \true) && \count($funcCallOrEmptyNodeArgs) === 3) {
            unset($funcCallOrEmptyNodeArgs[2]);
            return $this->appendArgs($funcCallOrEmptyNodeArgs, $oldArguments);
        }
        if ($funcCallOrEmptyNodeName === 'is_a') {
            $newArgs = [$funcCallOrEmptyNodeArgs[1], $funcCallOrEmptyNodeArgs[0]];
            return $this->appendArgs($newArgs, $oldArguments);
        }
        return $this->appendArgs($funcCallOrEmptyNodeArgs, $oldArguments);
    }
}
