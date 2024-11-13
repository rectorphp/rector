<?php

declare (strict_types=1);
namespace Rector\NodeDecorator;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class StatementDepthAttributeDecorator
{
    /**
     * @param ClassMethod[] $classMethods
     */
    public static function decorateClassMethods(array $classMethods) : void
    {
        foreach ($classMethods as $classMethod) {
            foreach ((array) $classMethod->stmts as $methodStmt) {
                $methodStmt->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, \true);
                if ($methodStmt instanceof Expression) {
                    $methodStmt->expr->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, \true);
                }
            }
        }
    }
}
