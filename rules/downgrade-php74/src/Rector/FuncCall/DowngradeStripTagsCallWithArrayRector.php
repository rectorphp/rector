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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert 2nd param to `strip_tags` from array to string', [
            new CodeSample(
                <<<'CODE_SAMPLE'
define ('SOME_DEFINE', ['a', 'p']);

class SomeClass
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Consts: if array, change to string
        strip_tags($string, SOME_DEFINE);
        strip_tags($string, self::SOME_CONST);

        // Function/method call: if array, change to string
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
define ('SOME_DEFINE', ['a', 'p']);

class SomeClass
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Consts: if array, change to string
        strip_tags($string, is_array(SOME_DEFINE) ? '<' . implode('><', SOME_DEFINE) . '>' : SOME_DEFINE);
        strip_tags($string, is_array(self::SOME_CONST) ? '<' . implode('><', self::SOME_CONST) . '>' : self::SOME_CONST);

        // Function/method call: if array, change to string
        $tags = getTags();
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);
        $tags = $this->getTags();
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);
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
            $newExpr = $this->getConvertArrayToStringFuncCall($allowableTagsParam);
        } elseif ($allowableTagsParam instanceof Variable || $allowableTagsParam instanceof PropertyFetch || $allowableTagsParam instanceof ConstFetch || $allowableTagsParam instanceof ClassConstFetch) {
            // If it is a variable or a const (other than null), add logic to maybe convert to string
            $newExpr = $this->getIfArrayConvertArrayToStringFuncCall($allowableTagsParam);
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
            $newExpr = $this->getIfArrayConvertArrayToStringFuncCall($newVariable);
        }

        // Replace the arg with a new one
        array_splice($node->args, 1, 1, [new Arg($newExpr)]);
        return $node;
    }

    private function getConvertArrayToStringFuncCall(Expr $allowableTagsParam): Expr
    {
        return new Concat(
            new Concat(
                new String_('<'),
                new FuncCall(new Name('implode'), [new String_('><'), $allowableTagsParam])
            ),
            new String_('>')
        );
    }

    private function getIfArrayConvertArrayToStringFuncCall(Expr $allowableTagsParam): Expr
    {
        return new Ternary(
            new BooleanAnd(
                new NotIdentical($allowableTagsParam, $this->createNull()),
                new FuncCall(new Name('is_array'), [$allowableTagsParam])
            ),
            $this->getConvertArrayToStringFuncCall($allowableTagsParam),
            $allowableTagsParam
        );
    }

    /**
     * @param FuncCall $node
     */
    private function shouldRefactor(Node $node): bool
    {
        if (! $this->isFuncCallName($node, 'strip_tags')) {
            return false;
        }

        // If param not provided, do nothing
        if (count($node->args) < 2) {
            return false;
        }

        // Process anything other than String and null (eg: variables, function calls)
        $allowableTagsParam = $node->args[1]->value;

        // Skip for string
        if ($allowableTagsParam instanceof String_) {
            return false;
        }

        // Skip for null
        if ($this->isNull($allowableTagsParam)) {
            return false;
        }
        // Allow for everything else (Array_, Variable, PropertyFetch, ConstFetch, ClassConstFetch, FuncCall, MethodCall, Coalesce, Ternary, others?)
        return true;
    }
}
