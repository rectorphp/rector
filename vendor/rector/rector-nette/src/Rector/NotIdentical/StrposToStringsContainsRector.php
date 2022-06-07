<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/CubLi
 * @see https://github.com/nette/utils/blob/bd961f49b211997202bda1d0fbc410905be370d4/src/Utils/Strings.php#L81
 *
 * @see \Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector\StrposToStringsContainsRectorTest
 */
final class StrposToStringsContainsRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use Nette\\Utils\\Strings over bare string-functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return strpos($name, 'Hi') !== false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return \Nette\Utils\Strings::contains($name, 'Hi');
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
        return [NotIdentical::class, Identical::class];
    }
    /**
     * @param NotIdentical|Identical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $funcCall = $this->matchStrposInComparisonToFalse($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        if (isset($funcCall->args[2]) && !$this->valueResolver->isValue($funcCall->args[2]->value, 0)) {
            return null;
        }
        $containsStaticCall = $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', 'contains');
        $containsStaticCall->args[0] = $funcCall->args[0];
        $containsStaticCall->args[1] = $funcCall->args[1];
        if ($node instanceof Identical) {
            return new BooleanNot($containsStaticCall);
        }
        return $containsStaticCall;
    }
    private function matchStrposInComparisonToFalse(BinaryOp $binaryOp) : ?Expr
    {
        if ($this->valueResolver->isFalse($binaryOp->left)) {
            $rightExpr = $binaryOp->right;
            if ($this->isStrposFuncCall($rightExpr)) {
                return $rightExpr;
            }
        }
        if ($this->valueResolver->isFalse($binaryOp->right)) {
            $leftExpr = $binaryOp->left;
            if ($this->isStrposFuncCall($leftExpr)) {
                return $leftExpr;
            }
        }
        return null;
    }
    private function isStrposFuncCall(Expr $expr) : bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        return $this->isName($expr, 'strpos');
    }
}
