<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Constant\ConstantStringType;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/XPEEl
 * @changelog https://3v4l.org/ObNQZ
 * @see \Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\BinaryOpBetweenNumberAndStringRectorTest
 */
final class BinaryOpBetweenNumberAndStringRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::BINARY_OP_NUMBER_STRING;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change binary operation between some number + string to PHP 7.1 compatible version', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0;
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
        return [BinaryOp::class];
    }
    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Concat) {
            return null;
        }
        if ($node instanceof Coalesce) {
            return null;
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($node->left)) {
            return null;
        }
        if ($this->exprAnalyzer->isNonTypedFromParam($node->right)) {
            return null;
        }
        if ($this->isStringOrStaticNonNumericString($node->left) && $this->nodeTypeResolver->isNumberType($node->right)) {
            $node->left = new LNumber(0);
            return $node;
        }
        if ($this->isStringOrStaticNonNumericString($node->right) && $this->nodeTypeResolver->isNumberType($node->left)) {
            $node->right = new LNumber(0);
            return $node;
        }
        return null;
    }
    private function isStringOrStaticNonNumericString(Expr $expr) : bool
    {
        // replace only scalar values, not variables/constants/etc.
        if (!$expr instanceof Scalar && !$expr instanceof Variable) {
            return \false;
        }
        if ($expr instanceof Line) {
            return \false;
        }
        if ($expr instanceof String_) {
            return !\is_numeric($expr->value);
        }
        $exprStaticType = $this->getType($expr);
        if ($exprStaticType instanceof ConstantStringType) {
            return !\is_numeric($exprStaticType->getValue());
        }
        return \false;
    }
}
