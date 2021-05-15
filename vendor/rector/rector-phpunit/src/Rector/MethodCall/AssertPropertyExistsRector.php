<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Renaming\NodeManipulator\IdentifierManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\AssertPropertyExistsRectorTest
 */
final class AssertPropertyExistsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_WITH_OBJECT_MAP = ['assertTrue' => 'assertObjectHasAttribute', 'assertFalse' => 'assertObjectNotHasAttribute'];
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_WITH_CLASS_MAP = ['assertTrue' => 'assertClassHasAttribute', 'assertFalse' => 'assertClassNotHasAttribute'];
    /**
     * @var \Rector\Renaming\NodeManipulator\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\Renaming\NodeManipulator\IdentifierManipulator $identifierManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertTrue(property_exists(new Class, "property"), "message");', '$this->assertClassHasAttribute("property", "Class", "message");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertFalse(property_exists(new Class, "property"), "message");', '$this->assertClassNotHasAttribute("property", "Class", "message");')]);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertTrue', 'assertFalse'])) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($firstArgumentValue, 'property_exists')) {
            return null;
        }
        $propertyExistsMethodCall = $node->args[0]->value;
        if (!$propertyExistsMethodCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        $firstArgument = $propertyExistsMethodCall->args[0];
        $secondArgument = $propertyExistsMethodCall->args[1];
        if ($firstArgument->value instanceof \PhpParser\Node\Expr\Variable) {
            $secondArg = new \PhpParser\Node\Expr\Variable($firstArgument->value->name);
            $map = self::RENAME_METHODS_WITH_OBJECT_MAP;
        } elseif ($firstArgument->value instanceof \PhpParser\Node\Expr\New_) {
            $secondArg = $this->getName($firstArgument->value->class);
            $map = self::RENAME_METHODS_WITH_CLASS_MAP;
        } else {
            return null;
        }
        if (!$secondArgument->value instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        unset($node->args[0]);
        $newArgs = $this->nodeFactory->createArgs([$secondArgument->value->value, $secondArg]);
        $node->args = $this->appendArgs($newArgs, $node->args);
        $this->identifierManipulator->renameNodeWithMap($node, $map);
        return $node;
    }
}
