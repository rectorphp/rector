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
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\AssertPropertyExistsRectorTest
 */
final class AssertPropertyExistsRector extends AbstractRector
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
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertFalse(property_exists(new Class, "property"));
$this->assertTrue(property_exists(new Class, "property"));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertClassHasAttribute("property", "Class");
$this->assertClassNotHasAttribute("property", "Class");
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertTrue', 'assertFalse'])) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        if (!$firstArgumentValue instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($firstArgumentValue, 'property_exists')) {
            return null;
        }
        $propertyExistsMethodCall = $node->args[0]->value;
        if (!$propertyExistsMethodCall instanceof FuncCall) {
            return null;
        }
        $firstArgument = $propertyExistsMethodCall->args[0];
        $secondArgument = $propertyExistsMethodCall->args[1];
        if ($firstArgument->value instanceof Variable) {
            $secondArg = new Variable($firstArgument->value->name);
            $map = self::RENAME_METHODS_WITH_OBJECT_MAP;
        } elseif ($firstArgument->value instanceof New_) {
            $secondArg = $this->getName($firstArgument->value->class);
            $map = self::RENAME_METHODS_WITH_CLASS_MAP;
        } else {
            return null;
        }
        if (!$secondArgument->value instanceof String_) {
            return null;
        }
        unset($node->args[0]);
        $newArgs = $this->nodeFactory->createArgs([$secondArgument->value->value, $secondArg]);
        $node->args = $this->appendArgs($newArgs, $node->args);
        $this->identifierManipulator->renameNodeWithMap($node, $map);
        return $node;
    }
}
