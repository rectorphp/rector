<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
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
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\DowngradeStripTagsCallWithArrayRectorTest
 */
final class DowngradeStripTagsCallWithArrayRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert 2nd param to `strip_tags` from array to string',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        $allowableTagsParam = $node->args[1]->value;

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
            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix(
                $node,
                'AllowableTags',
                'allowableTags',
                $node->getAttribute(AttributeKey::SCOPE)
            );
            // Assign the value to the variable
            $newVariable = new Variable($variableName);
            $this->addNodeBeforeNode(new Assign($newVariable, $allowableTagsParam), $node);

            // Apply refactor on the variable
            $newExpr = $this->createIsArrayTernaryFromExpression($newVariable);
        }

        // Replace the arg with a new one
        array_splice($node->args, 1, 1, [new Arg($newExpr)]);
        return $node;
    }

    private function shouldRefactor(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'strip_tags')) {
            return false;
        }

        // If param not provided, do nothing
        if (count($funcCall->args) < 2) {
            return false;
        }

        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $funcCall->args[1]->value;

        // Skip for string
        if ($allowableTagsParam instanceof String_) {
            return false;
        }
        // Skip for null
        // Allow for everything else (Array_, Variable, PropertyFetch, ConstFetch, ClassConstFetch, FuncCall, MethodCall, Coalesce, Ternary, others?)
        return ! $this->valueResolver->isNull($allowableTagsParam);
    }

    /**
     * @param Array_|Variable|PropertyFetch|ConstFetch|ClassConstFetch $expr
     */
    private function createArrayFromString(Expr $expr): Concat
    {
        $args = [new Arg(new String_('><')), new Arg($expr)];
        $implodeFuncCall = new FuncCall(new Name('implode'), $args);

        $concat = new Concat(new String_('<'), $implodeFuncCall);
        return new Concat($concat, new String_('>'));
    }

    /**
     * @param Variable|PropertyFetch|ConstFetch|ClassConstFetch $expr
     */
    private function createIsArrayTernaryFromExpression(Expr $expr): Ternary
    {
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($expr)]);
        $nullNotIdentical = new NotIdentical($expr, $this->nodeFactory->createNull());
        $booleanAnd = new BooleanAnd($nullNotIdentical, $isArrayFuncCall);

        return new Ternary($booleanAnd, $this->createArrayFromString($expr), $expr);
    }
}
