<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\NeverType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class NeverFuncCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function hasNeverFuncCall($functionLike) : bool
    {
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($this->isWithNeverTypeExpr($stmt)) {
                return \true;
            }
        }
        return \false;
    }
    public function isWithNeverTypeExpr(Stmt $stmt) : bool
    {
        if ($stmt instanceof Expression) {
            $stmt = $stmt->expr;
        }
        if ($stmt instanceof Stmt) {
            return \false;
        }
        $stmtType = $this->nodeTypeResolver->getNativeType($stmt);
        return $stmtType instanceof NeverType;
    }
}
