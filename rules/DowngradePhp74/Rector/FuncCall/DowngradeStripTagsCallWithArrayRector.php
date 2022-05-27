<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends \Rector\Core\Rector\AbstractRector
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert 2nd param to `strip_tags` from array to string', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables/consts/properties: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Default case (eg: function call): externalize to var, then if array, change to string
        strip_tags($string, getTags());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables/consts/properties: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags !== null && is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Default case (eg: function call): externalize to var, then if array, change to string
        $expr = getTags();
        strip_tags($string, is_array($expr) ? '<' . implode('><', $expr) . '>' : $expr);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipFuncCall($node)) {
            return null;
        }
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        $allowableTagsParam = $secondArg->value;
        if ($allowableTagsParam instanceof \PhpParser\Node\Expr\Array_) {
            // If it is an array, convert it to string
            $newExpr = $this->createArrayFromString($allowableTagsParam);
        } elseif ($allowableTagsParam instanceof \PhpParser\Node\Expr\Variable || $allowableTagsParam instanceof \PhpParser\Node\Expr\PropertyFetch || $allowableTagsParam instanceof \PhpParser\Node\Expr\ConstFetch || $allowableTagsParam instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            // If it is a variable or a const (other than null), add logic to maybe convert to string
            $newExpr = $this->createIsArrayTernaryFromExpression($allowableTagsParam);
        } else {
            // It is a function or method call, ternary or coalesce, or any other:
            // Assign the value to a variable
            // First obtain a variable name that does not exist in the node (to not override its value)
            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix($node, 'AllowableTags', 'allowableTags', $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE));
            // Assign the value to the variable
            $newVariable = new \PhpParser\Node\Expr\Variable($variableName);
            $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Expr\Assign($newVariable, $allowableTagsParam), $node, $this->file->getSmartFileInfo());
            // Apply refactor on the variable
            $newExpr = $this->createIsArrayTernaryFromExpression($newVariable);
        }
        // Replace the arg with a new one
        \array_splice($node->args, 1, 1, [new \PhpParser\Node\Arg($newExpr)]);
        return $node;
    }
    private function shouldSkipFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'strip_tags')) {
            return \true;
        }
        // If param not provided, do nothing
        if (\count($funcCall->args) < 2) {
            return \true;
        }
        if (!isset($funcCall->args[1])) {
            return \true;
        }
        if (!$funcCall->args[1] instanceof \PhpParser\Node\Arg) {
            return \true;
        }
        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $funcCall->args[1]->value;
        // Skip for string
        if ($allowableTagsParam instanceof \PhpParser\Node\Scalar\String_) {
            return \true;
        }
        // already refactored
        if ($allowableTagsParam instanceof \PhpParser\Node\Expr\Ternary && $allowableTagsParam->if !== null) {
            return \true;
        }
        if ($allowableTagsParam instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return \true;
        }
        // Skip for null
        // Allow for everything else (Array_, Variable, PropertyFetch, ConstFetch, ClassConstFetch, FuncCall, MethodCall, Coalesce, Ternary, others?)
        return $this->valueResolver->isNull($allowableTagsParam);
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $expr
     */
    private function createArrayFromString($expr) : \PhpParser\Node\Expr\BinaryOp\Concat
    {
        $args = [new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('><')), new \PhpParser\Node\Arg($expr)];
        $implodeFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('implode'), $args);
        $concat = new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_('<'), $implodeFuncCall);
        return new \PhpParser\Node\Expr\BinaryOp\Concat($concat, new \PhpParser\Node\Scalar\String_('>'));
    }
    /**
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $expr
     */
    private function createIsArrayTernaryFromExpression($expr) : \PhpParser\Node\Expr\Ternary
    {
        $isArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('is_array'), [new \PhpParser\Node\Arg($expr)]);
        $nullNotIdentical = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, $this->nodeFactory->createNull());
        $booleanAnd = new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($nullNotIdentical, $isArrayFuncCall);
        return new \PhpParser\Node\Expr\Ternary($booleanAnd, $this->createArrayFromString($expr), $expr);
    }
}
