<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeFinder;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
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
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function find(Class_ $class) : ?Variable
    {
        foreach ($class->getMethods() as $classMethod) {
            $classMethodStmts = $classMethod->getStmts();
            if ($classMethodStmts === null) {
                continue;
            }
            foreach ($classMethodStmts as $classMethodStmt) {
                if (!$classMethodStmt instanceof Expression) {
                    continue;
                }
                if (!$classMethodStmt->expr instanceof Assign) {
                    continue;
                }
                $var = $classMethodStmt->expr->var;
                $expr = $classMethodStmt->expr->expr;
                if (!$var instanceof Variable) {
                    continue;
                }
                if (!$this->nodeTypeResolver->isObjectType($expr, new ObjectType('Nette\\Forms\\Form'))) {
                    continue;
                }
                return $var;
            }
        }
        return null;
    }
}
