<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\AssertTrueFalseInternalTypeToSpecificMethodRectorTest
 */
final class AssertTrueFalseInternalTypeToSpecificMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_FUNCTIONS_TO_TYPES = ['is_array' => 'array', 'is_bool' => 'bool', 'is_callable' => 'callable', 'is_double' => 'double', 'is_float' => 'float', 'is_int' => 'int', 'is_integer' => 'integer', 'is_iterable' => 'iterable', 'is_numeric' => 'numeric', 'is_object' => 'object', 'is_real' => 'real', 'is_resource' => 'resource', 'is_scalar' => 'scalar', 'is_string' => 'string'];
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = ['assertTrue' => 'assertInternalType', 'assertFalse' => 'assertNotInternalType'];
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator $identifierManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns true/false with internal type comparisons to their method name alternatives in PHPUnit TestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertTrue(is_{internal_type}($anything), "message");', '$this->assertInternalType({internal_type}, $anything, "message");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertFalse(is_{internal_type}($anything), "message");', '$this->assertNotInternalType({internal_type}, $anything, "message");')]);
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
        $oldMethods = \array_keys(self::RENAME_METHODS_MAP);
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, $oldMethods)) {
            return null;
        }
        /** @var FuncCall|Node $firstArgumentValue */
        $firstArgumentValue = $node->args[0]->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $functionName = $this->getName($firstArgumentValue);
        if (!isset(self::OLD_FUNCTIONS_TO_TYPES[$functionName])) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        return $this->moveFunctionArgumentsUp($node);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function moveFunctionArgumentsUp($node) : \PhpParser\Node
    {
        /** @var FuncCall $isFunctionNode */
        $isFunctionNode = $node->args[0]->value;
        $firstArgumentValue = $isFunctionNode->args[0]->value;
        $isFunctionName = $this->getName($isFunctionNode);
        $newArgs = [new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_(self::OLD_FUNCTIONS_TO_TYPES[$isFunctionName])), new \PhpParser\Node\Arg($firstArgumentValue)];
        $oldArguments = $node->args;
        unset($oldArguments[0]);
        $node->args = $this->appendArgs($newArgs, $oldArguments);
        return $node;
    }
}
