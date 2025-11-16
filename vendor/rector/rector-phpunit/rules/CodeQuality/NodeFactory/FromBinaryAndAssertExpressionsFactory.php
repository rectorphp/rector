<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\NodeFactory;
final class FromBinaryAndAssertExpressionsFactory
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Expr[] $exprs
     * @return Stmt[]
     */
    public function create(array $exprs): array
    {
        $assertMethodCalls = [];
        foreach ($exprs as $expr) {
            // implicit bool compare
            if ($expr instanceof MethodCall) {
                $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', 'assertTrue', [$expr]);
                continue;
            }
            if ($expr instanceof FuncCall && $this->nodeNameResolver->isName($expr, 'array_key_exists')) {
                $variableExpr = $expr->getArgs()[1]->value;
                $dimExpr = $expr->getArgs()[0]->value;
                $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', 'assertArrayHasKey', [$dimExpr, $variableExpr]);
                continue;
            }
            if ($expr instanceof Isset_) {
                foreach ($expr->vars as $issetVariable) {
                    if ($issetVariable instanceof ArrayDimFetch) {
                        $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', 'assertArrayHasKey', [$issetVariable->dim, $issetVariable->var]);
                    } else {
                        // not supported yet
                        return [];
                    }
                }
                continue;
            }
            if ($expr instanceof Instanceof_) {
                if ($expr->class instanceof FullyQualified) {
                    $classNameExpr = new ClassConstFetch(new FullyQualified($expr->class->name), 'class');
                } elseif ($expr->class instanceof Name) {
                    $classNameExpr = new ClassConstFetch(new Name($expr->class->name), 'class');
                } else {
                    $classNameExpr = $expr->class;
                }
                $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', 'assertInstanceOf', [$classNameExpr, $expr->expr]);
                continue;
            }
            if ($expr instanceof Identical || $expr instanceof Equal) {
                if ($expr->left instanceof FuncCall && $this->nodeNameResolver->isName($expr->left, 'count')) {
                    if ($expr->right instanceof Int_) {
                        $countedExpr = $expr->left->getArgs()[0]->value;
                        // create assertCount()
                        $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', 'assertCount', [$expr->right, $countedExpr]);
                        continue;
                    }
                    // unclear, fallback to no change
                    return [];
                }
                // create assertSame()
                $assertMethodCalls[] = $this->nodeFactory->createMethodCall('this', $expr instanceof Identical ? 'assertSame' : 'assertEquals', [$expr->right, $expr->left]);
            } else {
                // not supported expr
                return [];
            }
        }
        if ($assertMethodCalls === []) {
            return [];
        }
        // to keep order from binary
        $assertMethodCalls = array_reverse($assertMethodCalls);
        $stmts = [];
        foreach ($assertMethodCalls as $assertMethodCall) {
            $stmts[] = new Expression($assertMethodCall);
        }
        return $stmts;
    }
}
