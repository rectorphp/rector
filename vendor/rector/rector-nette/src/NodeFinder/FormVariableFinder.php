<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @see \Rector\Nette\Tests\NodeFinder\FormFinder\FormFinderTest
 */
final class FormVariableFinder
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function find(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Expr\Variable
    {
        foreach ($class->getMethods() as $classMethod) {
            $classMethodStmts = $classMethod->getStmts();
            if ($classMethodStmts === null) {
                continue;
            }
            foreach ($classMethodStmts as $classMethodStmt) {
                if (!$classMethodStmt instanceof \PhpParser\Node\Stmt\Expression) {
                    continue;
                }
                if (!$classMethodStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                    continue;
                }
                $var = $classMethodStmt->expr->var;
                $expr = $classMethodStmt->expr->expr;
                if (!$var instanceof \PhpParser\Node\Expr\Variable) {
                    continue;
                }
                if (!$this->nodeTypeResolver->isObjectType($expr, new \PHPStan\Type\ObjectType('Nette\\Forms\\Form'))) {
                    continue;
                }
                return $var;
            }
        }
        return null;
    }
}
