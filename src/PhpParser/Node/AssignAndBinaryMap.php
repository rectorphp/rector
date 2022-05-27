<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node;

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
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class AssignAndBinaryMap
{
    /**
     * @var array<class-string<BinaryOp>, class-string<BinaryOp>>
     */
    private const BINARY_OP_TO_INVERSE_CLASSES = [\PhpParser\Node\Expr\BinaryOp\Identical::class => \PhpParser\Node\Expr\BinaryOp\NotIdentical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class => \PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\Equal::class => \PhpParser\Node\Expr\BinaryOp\NotEqual::class, \PhpParser\Node\Expr\BinaryOp\NotEqual::class => \PhpParser\Node\Expr\BinaryOp\Equal::class, \PhpParser\Node\Expr\BinaryOp\Greater::class => \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class, \PhpParser\Node\Expr\BinaryOp\Smaller::class => \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class, \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class => \PhpParser\Node\Expr\BinaryOp\Smaller::class, \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class => \PhpParser\Node\Expr\BinaryOp\Greater::class];
    /**
     * @var array<class-string<AssignOp>, class-string<BinaryOp>>
     */
    private const ASSIGN_OP_TO_BINARY_OP_CLASSES = [\PhpParser\Node\Expr\AssignOp\BitwiseOr::class => \PhpParser\Node\Expr\BinaryOp\BitwiseOr::class, \PhpParser\Node\Expr\AssignOp\BitwiseAnd::class => \PhpParser\Node\Expr\BinaryOp\BitwiseAnd::class, \PhpParser\Node\Expr\AssignOp\BitwiseXor::class => \PhpParser\Node\Expr\BinaryOp\BitwiseXor::class, \PhpParser\Node\Expr\AssignOp\Plus::class => \PhpParser\Node\Expr\BinaryOp\Plus::class, \PhpParser\Node\Expr\AssignOp\Div::class => \PhpParser\Node\Expr\BinaryOp\Div::class, \PhpParser\Node\Expr\AssignOp\Mul::class => \PhpParser\Node\Expr\BinaryOp\Mul::class, \PhpParser\Node\Expr\AssignOp\Minus::class => \PhpParser\Node\Expr\BinaryOp\Minus::class, \PhpParser\Node\Expr\AssignOp\Concat::class => \PhpParser\Node\Expr\BinaryOp\Concat::class, \PhpParser\Node\Expr\AssignOp\Pow::class => \PhpParser\Node\Expr\BinaryOp\Pow::class, \PhpParser\Node\Expr\AssignOp\Mod::class => \PhpParser\Node\Expr\BinaryOp\Mod::class, \PhpParser\Node\Expr\AssignOp\ShiftLeft::class => \PhpParser\Node\Expr\BinaryOp\ShiftLeft::class, \PhpParser\Node\Expr\AssignOp\ShiftRight::class => \PhpParser\Node\Expr\BinaryOp\ShiftRight::class];
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
    public function getAlternative(\PhpParser\Node $node) : ?string
    {
        $nodeClass = \get_class($node);
        if ($node instanceof \PhpParser\Node\Expr\AssignOp) {
            return self::ASSIGN_OP_TO_BINARY_OP_CLASSES[$nodeClass] ?? null;
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp) {
            return $this->binaryOpToAssignClasses[$nodeClass] ?? null;
        }
        return null;
    }
    /**
     * @return class-string<BinaryOp>|null
     */
    public function getInversed(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?string
    {
        $nodeClass = \get_class($binaryOp);
        return self::BINARY_OP_TO_INVERSE_CLASSES[$nodeClass] ?? null;
    }
    public function getTruthyExpr(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if ($expr instanceof \PhpParser\Node\Expr\Cast\Bool_) {
            return $expr;
        }
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $expr;
        }
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PhpParser\Node\Expr\Cast\Bool_($expr);
        }
        $type = $scope->getType($expr);
        if ($type instanceof \PHPStan\Type\BooleanType) {
            return $expr;
        }
        return new \PhpParser\Node\Expr\Cast\Bool_($expr);
    }
}
