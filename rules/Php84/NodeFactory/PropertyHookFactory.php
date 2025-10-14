<?php

declare (strict_types=1);
namespace Rector\Php84\NodeFactory;

use PhpParser\Node\PropertyHook;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use RectorPrefix202510\Webmozart\Assert\Assert;
final class PropertyHookFactory
{
    public function create(ClassMethod $classMethod, string $propertyName): ?PropertyHook
    {
        $methodName = $classMethod->name->toString();
        if ($methodName === 'get' . ucfirst($propertyName)) {
            $methodName = 'get';
        } elseif ($methodName === 'set' . ucfirst($propertyName)) {
            $methodName = 'set';
        } else {
            return null;
        }
        Assert::notNull($classMethod->stmts);
        $soleStmt = $classMethod->stmts[0];
        // use sole Expr
        if (($soleStmt instanceof Expression || $soleStmt instanceof Return_) && $methodName !== 'set') {
            $body = $soleStmt->expr;
        } else {
            $body = [$soleStmt];
        }
        $setterPropertyHook = new PropertyHook($methodName, $body);
        $setterPropertyHook->params = $classMethod->params;
        return $setterPropertyHook;
    }
}
