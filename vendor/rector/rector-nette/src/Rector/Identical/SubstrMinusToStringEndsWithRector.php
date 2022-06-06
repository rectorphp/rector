<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Rector\Identical;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\UnaryMinus;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer;
use RectorPrefix20220606\Rector\Nette\ValueObject\FuncCallAndExpr;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\SubstrMinusToStringEndsWithRectorTest
 */
final class SubstrMinusToStringEndsWithRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SUBSTR = 'substr';
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\BinaryOpAnalyzer
     */
    private $binaryOpAnalyzer;
    public function __construct(BinaryOpAnalyzer $binaryOpAnalyzer)
    {
        $this->binaryOpAnalyzer = $binaryOpAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change substr function with minus to Strings::endsWith()', [new CodeSample(<<<'CODE_SAMPLE'
substr($var, -4) !== 'Test';
substr($var, -4) === 'Test';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
! \Nette\Utils\Strings::endsWith($var, 'Test');
\Nette\Utils\Strings::endsWith($var, 'Test');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $funcCallAndExpr = $this->binaryOpAnalyzer->matchFuncCallAndOtherExpr($node, self::SUBSTR);
        if (!$funcCallAndExpr instanceof FuncCallAndExpr) {
            return null;
        }
        $substrFuncCall = $funcCallAndExpr->getFuncCall();
        if (!$substrFuncCall->args[1]->value instanceof UnaryMinus) {
            return null;
        }
        /** @var UnaryMinus $unaryMinus */
        $unaryMinus = $substrFuncCall->args[1]->value;
        if (!$unaryMinus->expr instanceof LNumber) {
            return null;
        }
        $string = $funcCallAndExpr->getExpr();
        $wordLength = $unaryMinus->expr->value;
        if ($string instanceof String_ && \strlen($string->value) !== $wordLength) {
            return null;
        }
        $arguments = [$substrFuncCall->args[0]->value, $string];
        $staticCall = $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', 'endsWith', $arguments);
        if ($node instanceof Identical) {
            return $staticCall;
        }
        return new BooleanNot($staticCall);
    }
}
