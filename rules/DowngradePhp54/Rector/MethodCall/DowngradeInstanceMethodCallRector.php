<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/instance-method-call
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\DowngradeInstanceMethodCallRectorTest
 */
final class DowngradeInstanceMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade instance and method call/property access', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
echo (new \ReflectionClass('\\stdClass'))->getName();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = new \ReflectionClass('\\stdClass');
echo $object->getName();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch|MethodCall|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $variable = $this->createVariable($node);
        $expression = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, $node->var));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $node->var = $variable;
        // necessary to remove useless parentheses
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch $node
     */
    private function shouldSkip($node) : bool
    {
        if ($node->var instanceof \PhpParser\Node\Expr\New_) {
            return \false;
        }
        return !$node->var instanceof \PhpParser\Node\Expr\Clone_;
    }
    private function createVariable(\PhpParser\Node $node) : \PhpParser\Node\Expr\Variable
    {
        $currentStmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStmt instanceof \PhpParser\Node\Stmt) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $scope = $currentStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('object', $scope));
    }
}
