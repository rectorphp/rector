<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes strlen comparison to 0 to direct empty string compare', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $value)
    {
        $empty = strlen($value) === 0;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $value)
    {
        $empty = $value === '';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Greater::class, Smaller::class, Identical::class];
    }
    /**
     * @param Greater|Smaller|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->left instanceof FuncCall) {
            $funcCall = $node->left;
            $expr = $node->right;
        } elseif ($node->right instanceof FuncCall) {
            $expr = $node->left;
            $funcCall = $node->right;
        } else {
            return null;
        }
        if (!$this->isName($funcCall, 'strlen')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->valueResolver->isValue($expr, 0)) {
            return null;
        }
        if ($node instanceof Greater && $node->right instanceof FuncCall || $node instanceof Smaller && $node->left instanceof FuncCall) {
            return null;
        }
        $variable = $funcCall->getArgs()[0]->value;
        // Needs string cast if variable type is not string
        // see https://github.com/rectorphp/rector/issues/6700
        $isStringType = $this->nodeTypeResolver->getNativeType($variable)->isString()->yes();
        if (!$isStringType) {
            $variable = new Expr\Cast\String_($variable);
        }
        return $node instanceof Identical ? new Identical($variable, new String_('')) : new NotIdentical($variable, new String_(''));
    }
}
