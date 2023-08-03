<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\ShiftLeft;
use PhpParser\Node\Expr\BinaryOp\ShiftRight;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector\NumericReturnTypeFromStrictScalarReturnsRectorTest
 */
final class NumericReturnTypeFromStrictScalarReturnsRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change numeric return type based on strict returns type operations', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(int $first, int $second)
    {
        return $first - $second;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(int $first, int $second): int
    {
        return $first - $second;
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $return = $this->matchRootReturnWithExpr($node);
        if (!$return instanceof Return_) {
            return null;
        }
        if ($return->expr instanceof PreInc || $return->expr instanceof PostInc || $return->expr instanceof PostDec || $return->expr instanceof PreDec) {
            $exprType = $this->nodeTypeResolver->getNativeType($return->expr);
            if ($exprType instanceof IntegerType) {
                $node->returnType = new Identifier('int');
                return $node;
            }
            return null;
        }
        // @see https://chat.openai.com/share/a9e4fb74-5366-4c4c-9998-d6caeb8b5acc
        if ($return->expr instanceof Minus || $return->expr instanceof Plus || $return->expr instanceof Mul || $return->expr instanceof Mod || $return->expr instanceof BitwiseAnd || $return->expr instanceof ShiftRight || $return->expr instanceof ShiftLeft || $return->expr instanceof BitwiseOr) {
            return $this->refactorBinaryOp($return->expr, $node);
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function matchRootReturnWithExpr($functionLike) : ?Return_
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        foreach ($functionLike->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Expr) {
                return null;
            }
            return $stmt;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     * @return null|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure
     */
    private function refactorBinaryOp(BinaryOp $binaryOp, $functionLike)
    {
        $leftType = $this->nodeTypeResolver->getNativeType($binaryOp->left);
        $rightType = $this->nodeTypeResolver->getNativeType($binaryOp->right);
        if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
            $functionLike->returnType = new Identifier('int');
            return $functionLike;
        }
        if ($leftType instanceof FloatType && $rightType instanceof FloatType) {
            $functionLike->returnType = new Identifier('float');
            return $functionLike;
        }
        if ($binaryOp instanceof Mul) {
            if ($leftType instanceof FloatType && $rightType instanceof IntegerType) {
                $functionLike->returnType = new Identifier('float');
                return $functionLike;
            }
            if ($leftType instanceof IntegerType && $rightType instanceof FloatType) {
                $functionLike->returnType = new Identifier('float');
                return $functionLike;
            }
        }
        return null;
    }
}
