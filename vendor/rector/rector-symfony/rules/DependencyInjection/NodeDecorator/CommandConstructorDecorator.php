<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\NodeDecorator;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\ValueObject\MethodName;
final class CommandConstructorDecorator
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function decorate(Class_ $class) : void
    {
        // special case for command to keep parent constructor call
        if (!$this->nodeTypeResolver->isObjectType($class, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
            return;
        }
        $constuctClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constuctClassMethod instanceof ClassMethod) {
            return;
        }
        // empty stmts? add parent::__construct() to setup command
        if ((array) $constuctClassMethod->stmts === []) {
            $parentConstructStaticCall = new StaticCall(new Name('parent'), '__construct');
            $constuctClassMethod->stmts[] = new Expression($parentConstructStaticCall);
        }
    }
}
