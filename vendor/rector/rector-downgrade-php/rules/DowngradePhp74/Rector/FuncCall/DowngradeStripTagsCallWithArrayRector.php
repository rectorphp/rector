<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
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
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert 2nd argument in `strip_tags()` from array to string', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables/consts/properties: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);
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
        $secondArg = $node->getArgs()[1];
        $allowableTagsParam = $secondArg->value;
        if ($allowableTagsParam instanceof Array_) {
            // If it is an array, convert it to string
            $newExpr = $this->createArrayFromString($allowableTagsParam);
        } elseif ($allowableTagsParam instanceof Variable || $allowableTagsParam instanceof PropertyFetch || $allowableTagsParam instanceof ConstFetch || $allowableTagsParam instanceof ClassConstFetch) {
            // If it is a variable or a const (other than null), add logic to maybe convert to string
            $newExpr = $this->createIsArrayTernaryFromExpression($allowableTagsParam);
        } else {
            return null;
        }
        $secondArg->value = $newExpr;
        return $node;
    }
    private function shouldSkipFuncCall(FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'strip_tags')) {
            return \true;
        }
        // If param not provided, do nothing
        $secondArg = $funcCall->getArgs()[1] ?? null;
        if (!$secondArg instanceof Arg) {
            return \true;
        }
        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $secondArg->value;
        // Skip for string
        if ($allowableTagsParam instanceof String_) {
            return \true;
        }
        // already refactored
        if ($allowableTagsParam instanceof Ternary && $allowableTagsParam->if instanceof Expr) {
            return \true;
        }
        if ($allowableTagsParam instanceof Concat) {
            return \true;
        }
        // Skip for null
        // Allow for everything else...
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
