<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp74\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Naming\Naming\VariableNaming;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert 2nd param to `strip_tags` from array to string', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipFuncCall($node)) {
            return null;
        }
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        $allowableTagsParam = $secondArg->value;
        if ($allowableTagsParam instanceof Array_) {
            // If it is an array, convert it to string
            $newExpr = $this->createArrayFromString($allowableTagsParam);
        } elseif ($allowableTagsParam instanceof Variable || $allowableTagsParam instanceof PropertyFetch || $allowableTagsParam instanceof ConstFetch || $allowableTagsParam instanceof ClassConstFetch) {
            // If it is a variable or a const (other than null), add logic to maybe convert to string
            $newExpr = $this->createIsArrayTernaryFromExpression($allowableTagsParam);
        } else {
            // It is a function or method call, ternary or coalesce, or any other:
            // Assign the value to a variable
            // First obtain a variable name that does not exist in the node (to not override its value)
            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix($node, 'AllowableTags', 'allowableTags', $node->getAttribute(AttributeKey::SCOPE));
            // Assign the value to the variable
            $newVariable = new Variable($variableName);
            $this->nodesToAddCollector->addNodeBeforeNode(new Assign($newVariable, $allowableTagsParam), $node, $this->file->getSmartFileInfo());
            // Apply refactor on the variable
            $newExpr = $this->createIsArrayTernaryFromExpression($newVariable);
        }
        // Replace the arg with a new one
        \array_splice($node->args, 1, 1, [new Arg($newExpr)]);
        return $node;
    }
    private function shouldSkipFuncCall(FuncCall $funcCall) : bool
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
        if (!$funcCall->args[1] instanceof Arg) {
            return \true;
        }
        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $funcCall->args[1]->value;
        // Skip for string
        if ($allowableTagsParam instanceof String_) {
            return \true;
        }
        // already refactored
        if ($allowableTagsParam instanceof Ternary && $allowableTagsParam->if !== null) {
            return \true;
        }
        if ($allowableTagsParam instanceof Concat) {
            return \true;
        }
        // Skip for null
        // Allow for everything else (Array_, Variable, PropertyFetch, ConstFetch, ClassConstFetch, FuncCall, MethodCall, Coalesce, Ternary, others?)
        return $this->valueResolver->isNull($allowableTagsParam);
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $expr
     */
    private function createArrayFromString($expr) : Concat
    {
        $args = [new Arg(new String_('><')), new Arg($expr)];
        $implodeFuncCall = new FuncCall(new Name('implode'), $args);
        $concat = new Concat(new String_('<'), $implodeFuncCall);
        return new Concat($concat, new String_('>'));
    }
    /**
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $expr
     */
    private function createIsArrayTernaryFromExpression($expr) : Ternary
    {
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($expr)]);
        $nullNotIdentical = new NotIdentical($expr, $this->nodeFactory->createNull());
        $booleanAnd = new BooleanAnd($nullNotIdentical, $isArrayFuncCall);
        return new Ternary($booleanAnd, $this->createArrayFromString($expr), $expr);
    }
}
