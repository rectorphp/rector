<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
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
        if ($return->expr instanceof Minus || $return->expr instanceof Plus || $return->expr instanceof Mul) {
            $leftType = $this->getType($return->expr->left);
            $rightType = $this->getType($return->expr->right);
            if ($leftType instanceof IntegerType && $rightType instanceof IntegerType) {
                $node->returnType = new Identifier('int');
                return $node;
            }
            if ($leftType instanceof FloatType && $rightType instanceof FloatType) {
                $node->returnType = new Identifier('float');
                return $node;
            }
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
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
}
