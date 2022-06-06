<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PhpParser\Node;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\BitwiseAnd as AssignBitwiseAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\BitwiseOr as AssignBitwiseOr;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\BitwiseXor as AssignBitwiseXor;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Concat as AssignConcat;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Div as AssignDiv;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Minus as AssignMinus;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Mod as AssignMod;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Mul as AssignMul;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Plus as AssignPlus;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Pow as AssignPow;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\ShiftLeft as AssignShiftLeft;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\ShiftRight as AssignShiftRight;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BitwiseXor;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Div;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Equal;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Greater;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Minus;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Mod;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Mul;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotEqual;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Plus;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Pow;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\ShiftLeft;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\ShiftRight;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Smaller;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast\Bool_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class AssignAndBinaryMap
{
    /**
     * @var array<class-string<BinaryOp>, class-string<BinaryOp>>
     */
    private const BINARY_OP_TO_INVERSE_CLASSES = [Identical::class => NotIdentical::class, NotIdentical::class => Identical::class, Equal::class => NotEqual::class, NotEqual::class => Equal::class, Greater::class => SmallerOrEqual::class, Smaller::class => GreaterOrEqual::class, GreaterOrEqual::class => Smaller::class, SmallerOrEqual::class => Greater::class];
    /**
     * @var array<class-string<AssignOp>, class-string<BinaryOp>>
     */
    private const ASSIGN_OP_TO_BINARY_OP_CLASSES = [AssignBitwiseOr::class => BitwiseOr::class, AssignBitwiseAnd::class => BitwiseAnd::class, AssignBitwiseXor::class => BitwiseXor::class, AssignPlus::class => Plus::class, AssignDiv::class => Div::class, AssignMul::class => Mul::class, AssignMinus::class => Minus::class, AssignConcat::class => Concat::class, AssignPow::class => Pow::class, AssignMod::class => Mod::class, AssignShiftLeft::class => ShiftLeft::class, AssignShiftRight::class => ShiftRight::class];
    /**
     * @var array<class-string<BinaryOp>, class-string<BinaryOp>>
     */
    private $binaryOpToAssignClasses = [];
    public function __construct()
    {
        $this->binaryOpToAssignClasses = \array_flip(self::ASSIGN_OP_TO_BINARY_OP_CLASSES);
    }
    /**
     * @return class-string<BinaryOp>|null
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
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new Bool_($expr);
        }
        $type = $scope->getType($expr);
        if ($type instanceof BooleanType) {
            return $expr;
        }
        return new Bool_($expr);
    }
}
