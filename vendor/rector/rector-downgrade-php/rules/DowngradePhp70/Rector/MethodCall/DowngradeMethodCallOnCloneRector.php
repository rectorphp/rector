<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.others
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\DowngradeMethodCallOnCloneRectorTest
 */
final class DowngradeMethodCallOnCloneRector extends AbstractRector
{
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace (clone $obj)->call() to object assign and call', [new CodeSample(<<<'CODE_SAMPLE'
(clone $this)->execute();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = (clone $this);
$object->execute();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if (!$methodCall->var instanceof Clone_) {
            return null;
        }
        $clone = $methodCall->var;
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $newVariableName = $this->variableNaming->createCountedValueName('object', $scope);
        $variable = new Variable($newVariableName);
        $assign = new Assign($variable, $clone);
        $variableMethodCall = new MethodCall($variable, $methodCall->name);
        return [new Expression($assign), new Expression($variableMethodCall)];
    }
}
