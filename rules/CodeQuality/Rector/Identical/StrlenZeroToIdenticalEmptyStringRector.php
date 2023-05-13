<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->left instanceof FuncCall) {
            return $this->processIdentical($node->right, $node->left);
        }
        if ($node->right instanceof FuncCall) {
            return $this->processIdentical($node->left, $node->right);
        }
        return null;
    }
    private function processIdentical(Expr $expr, FuncCall $funcCall) : ?Identical
    {
        if (!$this->isName($funcCall, 'strlen')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->valueResolver->isValue($expr, 0)) {
            return null;
        }
        $variable = $funcCall->getArgs()[0]->value;
        // Needs string cast if variable type is not string
        // see https://github.com/rectorphp/rector/issues/6700
        $isStringType = $this->nodeTypeResolver->getNativeType($variable)->isString()->yes();
        if (!$isStringType) {
            return new Identical(new Expr\Cast\String_($variable), new String_(''));
        }
        return new Identical($variable, new String_(''));
    }
}
