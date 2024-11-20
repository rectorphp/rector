<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\BitwiseAnd as AssignBitwiseAnd;
use PhpParser\Node\Expr\AssignOp\BitwiseOr as AssignBitwiseOr;
use PhpParser\Node\Expr\AssignOp\BitwiseXor as AssignBitwiseXor;
use PhpParser\Node\Expr\AssignOp\Concat as AssignConcat;
use PhpParser\Node\Expr\AssignOp\Div as AssignDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignMinus;
use PhpParser\Node\Expr\AssignOp\Mod as AssignMod;
use PhpParser\Node\Expr\AssignOp\Mul as AssignMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignPlus;
use PhpParser\Node\Expr\AssignOp\Pow as AssignPow;
use PhpParser\Node\Expr\AssignOp\ShiftLeft as AssignShiftLeft;
use PhpParser\Node\Expr\AssignOp\ShiftRight as AssignShiftRight;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\BitwiseXor;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\BinaryOp\ShiftLeft;
use PhpParser\Node\Expr\BinaryOp\ShiftRight;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class AssignAndBinaryMap
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @var array<class-string<BinaryOp>, class-string<BinaryOp>>
     */
    private const BINARY_OP_TO_INVERSE_CLASSES = [Identical::class => NotIdentical::class, NotIdentical::class => Identical::class, Equal::class => NotEqual::class, NotEqual::class => Equal::class, Greater::class => SmallerOrEqual::class, Smaller::class => GreaterOrEqual::class, GreaterOrEqual::class => Smaller::class, SmallerOrEqual::class => Greater::class];
    /**
     * @var array<class-string<AssignOp>, class-string<BinaryOp>>
     */
    private const ASSIGN_OP_TO_BINARY_OP_CLASSES = [AssignBitwiseOr::class => BitwiseOr::class, AssignBitwiseAnd::class => BitwiseAnd::class, AssignBitwiseXor::class => BitwiseXor::class, AssignPlus::class => Plus::class, AssignDiv::class => Div::class, AssignMul::class => Mul::class, AssignMinus::class => Minus::class, AssignConcat::class => Concat::class, AssignPow::class => Pow::class, AssignMod::class => Mod::class, AssignShiftLeft::class => ShiftLeft::class, AssignShiftRight::class => ShiftRight::class];
    /**
     * @var array<class-string<BinaryOp>, class-string<AssignOp>>
     */
    private array $binaryOpToAssignClasses = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        /** @var array<class-string<BinaryOp>, class-string<AssignOp>> $binaryClassesToAssignOp */
        $binaryClassesToAssignOp = \array_flip(self::ASSIGN_OP_TO_BINARY_OP_CLASSES);
        $this->binaryOpToAssignClasses = $binaryClassesToAssignOp;
    }
    /**
     * @return class-string<BinaryOp|AssignOp>|null
     */
    public function getAlternative(Node $node) : ?string
    {
        $nodeClass = \get_class($node);
        if ($node instanceof AssignOp) {
            return self::ASSIGN_OP_TO_BINARY_OP_CLASSES[$nodeClass] ?? null;
        }
        if ($node instanceof BinaryOp) {
            return $this->binaryOpToAssignClasses[$nodeClass] ?? null;
        }
        return null;
    }
    /**
     * @return class-string<BinaryOp>|null
     */
    public function getInversed(BinaryOp $binaryOp) : ?string
    {
        $nodeClass = \get_class($binaryOp);
        return self::BINARY_OP_TO_INVERSE_CLASSES[$nodeClass] ?? null;
    }
    public function getTruthyExpr(Expr $expr) : Expr
    {
        if ($expr instanceof Bool_) {
            return $expr;
        }
        if ($expr instanceof BooleanNot) {
            return $expr;
        }
        $exprType = $this->nodeTypeResolver->getType($expr);
        // $type = $scope->getType($expr);
        if ($exprType->isBoolean()->yes()) {
            return $expr;
        }
        return new Bool_($expr);
    }
}
